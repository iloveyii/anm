#!/usr/bin/perl 
# Student: Hazrat Ali, Acronym: haae09, Course: ANM ET2439

# class for log_table reading and writing
# Gets a device ID and interfaces array and read provious data from log_table, probes devices for current data
# store the result back to log_table
package LogTable;
use Net::SNMP;
use POSIX; 
use DBI;
use Date::Parse; 
use warnings;
use strict;


# Global Vars
my $DEBUG = 0; # if debug is true (1) all output is printed
my (%ifInOctetsPrev, %ifOutOctetsPrev, %inRatePrev, %inRateAveragePrev, %outRatePrev, %outRateAveragePrev, %accumInPrev, %accumOutPrev); # FOR previous readings from DB
my (%ifInOctetsCur, %ifOutOctetsCur, %inRateCur, %inRateAverageCur, %outRateCur, %outRateAverageCur, %accumInCur, %accumOutCur); # FOR current readings from devices the script is probing now
my (%firstUpdatedPrev, %lastUpdatedPrev); # Since these also come from DB therefore suffixed as Prev
my $dbh;
my $LOGTABLE; 
# constructor
sub new {
    my ($class) = shift;
    my $self = {
		_argc => shift,
		# _interfaces => shift,
    };
	
    bless $self, $class;
    return $self;
}

# Run Once
sub runOnce {
	# I got the following values from the calling class
	my ($self, $LOG_TABLE, $db, $ip, $port, $community, $deviceID, $interface) = @_;
	my @interfacesArr = @{$interface}; # convert to array
	$dbh = $db;
	$LOGTABLE =$LOG_TABLE; my $session;
	print "Inside LogTable object \n";
	print "Check LOGTABLE create if not exist \n";
	$self->createTableIfNotExist($LOGTABLE);
	# check if interfaces eq 'all'
	if($interfacesArr[0] eq 'all') {
		# 1: find no of interfaces on this device
		$session = $self->connectSnmp($ip, $port, $community);
		my $noInterfaces = $self->getNumberOfInterfaces($session);
		printf("No of interfaces = $noInterfaces \n"); 
		# 2: update interfaces array
		@interfacesArr=(1 .. $noInterfaces);	
	}
	
	## I have to do the following 5 tasks one by one
	########## 01: Read previous values from DB for the given device
	printf "Reading previous values from Database for IP= $ip ... ";
	$self->readPreviousDataFromDB($db, $deviceID, \@interfacesArr); print "Done !!! \n";
	########## 02: Connect snmp for the given IP
	printf "Connecting to SNMP for IP= $ip ... ";
	$session = $self->connectSnmp($ip, $port, $community);print "Done !!! \n";
	########## 03: Probe the Device on the provided interfacesArr
	# now probe for new data using array @interfacesArr and not db interfaces, bcz @interfaces may contain new interfaces added now
	printf "Getting counters (octects) for IP= $ip ... ";
	$self->getOctets($session, \@interfacesArr); print "Done !!! \n";
	########## 04: Compute the counters
	printf "Computing counters from previous data from DB and the new data via SNMP for IP= $ip ... ";
	$self->computeInOut(\@interfacesArr); print "Done !!! \n";
	########## 05: Save Current Data to DB
	printf "Saving back the computed counters to database for IP= $ip ... ";
	$self->saveTrafficToDB($deviceID, \@interfacesArr); print "Done !!! \n";
	return 1;
}

# find number of interfaces of the said ip
sub getNumberOfInterfaces {
	my ($self,$session) = @_;
	my $noInt = $session->get_request("1.3.6.1.2.1.2.1.0");
	die "request error: ".$session->error unless (defined $noInt);
	# get the no of interfaces
	my $noInterface=$noInt->{"1.3.6.1.2.1.2.1.0"};
	return $noInterface;
}

# Read previous counters from DB
sub readPreviousDataFromDB {
	my ($self, $db, $deviceID, $interface) = @_;
	my @interfaces = @{$interface}; # convert to array
	my $csv_interfaces = join(',', @interfaces); # convert to csv for mysql query
	if($DEBUG) { printf("In class LogTable I received: id= $deviceID and interfaces= @interfaces, csv_interfaces = $csv_interfaces \n"); }

	my $sql = "SELECT * FROM $LOGTABLE where deviceID= $deviceID and interface in ($csv_interfaces);";
	if ($DEBUG) { print $sql. "\n"; }
	my $sth = $dbh->prepare($sql);
	$sth->execute  or die "SQL Error: $DBI::errstr\n"; 
	%ifInOctetsPrev = (); %ifOutOctetsPrev=(); # clear previous
	
	while (my @data = $sth->fetchrow_array()) {
		my ($id,$deviceID,$interface,$inRate,$outRate, $inRateAverage, $outRateAverage, $inOctets, $outOctets, $accumIn, $accumOut, $firstUp, $lastUp) = @data;
		if ($DEBUG) { print "rows: ($id,$deviceID,$interface,$inRate,$outRate, $inRateAverage, $outRateAverage, $inOctets, $outOctets, $accumIn, $accumOut, $firstUp, $lastUp) \n"; }
		# we got previous data
		$ifInOctetsPrev{$interface} = $inOctets;  $inRatePrev{$interface} = $inRate ; $inRateAveragePrev{$interface} =  $inRateAverage; $accumInPrev{$interface} =  $accumIn;
		$ifOutOctetsPrev{$interface}= $outOctets; $outRatePrev{$interface} =  $outRate; $outRateAveragePrev{$interface} =  $outRateAverage;  $accumOutPrev{$interface} = $accumOut;
		 
		$firstUpdatedPrev{$interface} = $firstUp; $lastUpdatedPrev{$interface} = $lastUp;
    }
	return 1;
}

# connect SNMP
sub connectSnmp {
    my ($self, $ip,$port, $community) = @_;
	# Declare SNMP client 
	# requires a hostname and a community string as its arguments
	my ($session,$error) = Net::SNMP->session(Hostname => $ip,
							    Community => $community,
							    Nonblocking => 0,
								Translate => 0,
								Timeout => '1',
								Retries => '0',
								Port => $port,
	);
	die "session error: $error" unless ($session);
	return $session;
}

# gets octects from the specified interfaces
sub getOctets {
	my ($self,$session, $interface) = @_;
	my @interfaces = @{$interface}; # convert to array
	my @ifInAll=(); my @ifOutAll=(); 
	my $i; my $int; 
	foreach $int(@interfaces) {
		push(@ifInAll,"1.3.6.1.2.1.2.2.1.10." . $int );	 				
		push(@ifOutAll,"1.3.6.1.2.1.2.2.1.16." . $int ); 
	}

	my @probe_all_interfaces = (@ifInAll,@ifOutAll);	
	my	$session_response = $session->get_request(-varbindlist => \@probe_all_interfaces);
	snmp_dispatcher(); 
	$i = 0; 
	%ifInOctetsCur = ();  %ifOutOctetsCur = (); # clear previous values
	foreach $int(@interfaces) {	
		$ifInOctetsCur{$int} = $session_response->{$ifInAll[$i]}; 
		# %ifInOctetsCur = ($int => $session_response->{$ifInAll[$i]});
		$ifOutOctetsCur{$int} =  $session_response->{$ifOutAll[$i]};
		if($DEBUG)  { print("int= $int , for i= $i session_response= " . $session_response->{$ifInAll[$i]} . "\n");}
		$i++;
	}
	return 1;
}

# computes byte flow of in and out and handle counters wrapping 
sub computeInOut {
	my ($self, $interface) = @_;
	my @interfaces = @{$interface}; # convert to array
	my $int;
	# initialize
	%inRateCur = (); %inRateAverageCur = (); %outRateCur = (); %outRateAverageCur = (); %accumInCur = (); %accumOutCur=();
	my $delay = 1; my $inDiff;  my $outDiff;
	# my @sorted_interfaces = sort @interfaces;
	foreach $int (@interfaces) {
		
		if(exists($lastUpdatedPrev{$int})) { # check if this interface exists in DB already
			######### 1: we first compute the delay time from DB lastUpdatedPrev time and current time, This is per interface :)
			my $start_time = str2time($lastUpdatedPrev{$int}); # This is the time of the last update, convert it to unix timestamp
			my $end_time = time;
			$delay = $end_time - $start_time;

			######### 2: for IN
			$inDiff = ($ifInOctetsCur{"$int"} - $ifInOctetsPrev{$int}); # the diff of octets
			# handle wrapping
			if ( $ifInOctetsCur{$int} < $ifInOctetsPrev{$int} ) { # if current reading is less than previous then wrapping occurred
				$inDiff = (((2**32) - 1) - $ifInOctetsPrev{$int}) + $ifInOctetsCur{$int};
			}
			$inRateCur{$int} = ceil($inDiff / $delay);
			$inRateAverageCur{$int} = ceil(($inRateAveragePrev{$int} + $inRateCur{$int}) / 2);
			$accumInCur{$int} =  $accumInPrev{$int} + $inRateCur{$int};
			
			######### 3: for OUT
			$outDiff = ($ifOutOctetsCur{$int} - $ifOutOctetsPrev{$int});
			# handle wrapping
			if ( $ifOutOctetsCur{$int} < $ifOutOctetsPrev{$int} ) {
				$outDiff = (((2**32) - 1) - $ifOutOctetsPrev{$int}) + $ifOutOctetsCur{$int};
			}
			$outRateCur{$int} =  ceil($outDiff / $delay);
			$outRateAverageCur{$int} = ceil(($outRateAveragePrev{$int} + $outRateCur{$int}) / 2);
			$accumOutCur{$int} =  $accumOutPrev{$int} + $outRateCur{$int};
		} else { # if interface does not exist, then its new interface, assume delay =1 and initialize other vars TOO
			$delay = 1; 
			$inDiff = 0; $outDiff = 0;
			$ifInOctetsPrev{$int}=0; $ifOutOctetsPrev{$int}=0; $inRatePrev{$int}=0; $inRateAveragePrev{$int}=0; $outRatePrev{$int}=0; $outRateAveragePrev{$int}=0; $accumInPrev{$int}=0; $accumOutPrev{$int}=0; # FOR previous readings from DB
			# for first iteration nothing is correct (except In|Out octets) since there is no previous octets in DB and hence no computation can be done
			$inRateCur{$int}=0; $inRateAverageCur{$int}=0; $outRateCur{$int}=0; $outRateAverageCur{$int}=0; $accumInCur{$int}=0; $accumOutCur{$int}=0; # Assume current readings for first iteration
		}
		
		if($DEBUG) { printf("int=$int, delay=$delay, inDiff=$inDiff, inRateCur=$inRateCur{$int}, outRateCur=$outRateCur{$int}, inRateAverageCur=$inRateAverageCur{$int}, outRateAverageCur=$outRateAverageCur{$int}, ifInOctetsCur=$ifInOctetsCur{$int}, ifOutOctetsCur=$ifOutOctetsCur{$int}, accumInCur=$accumInCur{$int}, accumOutCur=$accumOutCur{$int} \n"); }
	}
}

# Save Traffic to DB
sub saveTrafficToDB { 
	my ($self, $deviceID, $interface) = @_;
	my @interfaces = @{$interface}; # convert to array
	my $int = 0;
	
	foreach $int(@interfaces) {
		my $sql = "INSERT INTO $LOGTABLE (id, deviceID, interface, inRate, outRate, inRateAverage, outRateAverage, inOctets, outOctets, accumIn, accumOut, firstUpdated, lastUpdated) 
		VALUES (NULL,$deviceID, $int, $inRateCur{$int}, $outRateCur{$int}, $inRateAverageCur{$int}, $outRateAverageCur{$int},
		$ifInOctetsCur{$int}, $ifOutOctetsCur{$int}, $accumInCur{$int}, $accumOutCur{$int},
		NOW(), NOW()
		)
		ON DUPLICATE KEY UPDATE
		inRate = $inRateCur{$int}, outRate = $outRateCur{$int},
		inRateAverage = $inRateAverageCur{$int}, outRateAverage = $outRateAverageCur{$int},
		inOctets = $ifInOctetsCur{$int}, outOctets = $ifOutOctetsCur{$int},
		accumIn = $accumInCur{$int}, accumOut = $accumOutCur{$int},
		lastUpdated = NOW()
		;";
		# print $sql. "\n";
		my $sth = $dbh->prepare($sql);
		$sth->execute  or die "SQL Error: $DBI::errstr\n";
	}
	return 1;
} 

# create LOG_TABLE table if not exist
sub createTableIfNotExist {
	my ($self,$LOGTABLE) = @_;
	my $sql = "
		  CREATE TABLE IF NOT EXISTS `$LOGTABLE` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `deviceID` tinyint(4) DEFAULT NULL,
		  `interface` tinyint(4) DEFAULT NULL,
		  `inRate` int(11) DEFAULT NULL,
		  `outRate` int(11) DEFAULT NULL,
		  `inRateAverage` int(11) DEFAULT NULL,
		  `outRateAverage` int(11) DEFAULT NULL,
		  `inOctets` int(11) DEFAULT NULL,
		  `outOctets` int(11) DEFAULT NULL,
		  `accumIn` int(11) DEFAULT NULL,
		  `accumOut` int(11) DEFAULT NULL,
		  `firstUpdated` datetime DEFAULT NULL,
		  `lastUpdated` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `deviceID_UNIQUE` (`deviceID`,`interface`)
		) ENGINE=InnoDB AUTO_INCREMENT=733 DEFAULT CHARSET=latin1;
	";

	my $sth = $dbh->prepare($sql);
	$sth->execute  or die "SQL Error: $DBI::errstr\n";
}
1;