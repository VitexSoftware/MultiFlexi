#Schedule ad-hoc actions
* * * * *	multiflexi multiflexi-scheduler i

#Schedule AbraFlexi scripts once per hour
0 * * * *	multiflexi multiflexi-scheduler h

#Schedule AbraFlexi scripts once per day
0 0 * * *	multiflexi multiflexi-scheduler d

#Schedule AbraFlexi scripts once per week
0 0 * * 0	multiflexi multiflexi-scheduler w

#Schedule AbraFlexi scripts once per month
0 0 1 * *	multiflexi multiflexi-scheduler m

#Schedule AbraFlexi scripts once per quarter
0 0 1 1,4,7,10 *	multiflexi multiflexi-scheduler q

#Run AbraFlexi scripts once per year
0 0 1 1 *	multiflexi multiflexi-scheduler y
