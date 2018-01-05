#!/bin/bash



if [ "$#" -ne 2 ]; then
    echo "Illegal number of parameters"
    echo "Usage: $0 DATABASE USER"
	exit 7
fi

#PUBLIC
DATABASE=$1
USER=$2


#PRIVATE
PSQL="/Library/PostgreSQL/9.3/bin/psql"
LOADERDIR="loaders"

PSQLCMD="$PSQL $DATABASE $USER"

printf "[?] --- I'm gonnaaa fuuuuuuuuuckkkuppppp all the database named $DATABASE...u shure??? [y/N] "
read answer

if [ "$answer" != "y" ]
then
	echo "[>] --- ufaa.... that was close..you didn't sayed 'y'"
	exit 0
fi

echo "[I] --- oohhh welll here we goooo"
sleep 3s


if [ ! -d "$LOADERDIR" ]; then
	echo "[!] --- loader directory does not exist....you must have a loader dir where you place the cute sql scripts to fuck up everything"
	exit 1
fi

echo "[I] --- Dropping the schema"
$PSQLCMD -c "drop schema public cascade;"

echo "[I] --- Creating empty schema"
$PSQLCMD -c "create schema public;"



#EXECUTING ALL SCRIPTS
for script in `ls $LOADERDIR`
do
	echo "[I] --- Executing $script"
	$PSQLCMD -f $LOADERDIR/$script

done

#QUITTING EVERYTHING
echo "[I] --- All scripts terminated"
exit 66
