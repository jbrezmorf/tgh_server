#!/usr/bin/python
# 
# Syntax:
#       spoj_run.py [--json-output <json_file>] <problem> <lang> <source>
#
# Submit file <source> to SPOJ judge as solution of problem with code 
# <problem> using language with code <lang>.
# Results are print to STDOUT, unless --json-output option is given.
# This option force output of JSON encoded results to given file <json_file>.
#


import sys
import time
import SpojApi
import os
import json

#netrc_file="/var/www/html/tgh.nti.tul.cz/py/.netrc"
netrc_file="/var/www/.netrc"
#netrc_file="/home/jb/.netrc"

log_file="/tmp/python_log"
try:
	log_file=open(log_file,"a")
	log_file.close()
except:
	#print ('cannot open to log file: ' + os.getcwd() + '/log')
	None

def log (s):
        # set to True for logging  
	if (False):
		print (s)
		print ('\n')
		try:
			log_file=open(log_file,"a")
			log_file.write(s)
			log_file.write("\n")
			log_file.close()
		except:
			#print ('cannot write to log file: ' + os.getcwd() + '/log')
			None


# read params
json_output=None
args=sys.argv
args=args[1:]
if (args[0] == "--json-output"):
    json_output=args[1]
    args=args[2:]
problem = args[0]
language = args[1]
sourcePath = args[2]


# get source
log ("reading file")
handle = open(sourcePath, 'r')
source = handle.read();



log ("creating api object")
spoj = SpojApi.SpojApi()

log ("logging to spoj")
#success = spoj.login ("tgh_2014", "frnak")
success = spoj.login_with_netrc(netrc_file)
if success == 0:
    exit (101)

log ("submitting solution")
id = spoj.submit (problem, source, language)
if id == -1:
    exit (102)

print 'id=%s' % id
log ("getting result")
data = spoj.get_sub_results(id)

result = spoj.result_strings['compiling']
while (spoj.active_status(result)) :
	time.sleep(0.5)
	data = spoj.get_sub_results(id)
	result = data['result']

if (json_output == None):
    # STDOUT output
    for key,value in data.iteritems() :
        print key + " = " + str(value)
else:
    json_handle=open(json_output, "w")
    json.dump(data,json_handle)
    json_handle.close()


exit (0);
