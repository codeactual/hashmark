#!/bin/sh

# Run from Hashmark root directory.

if [ -z "$1" ]; then
    echo "Usage: phpdoc.sh [target directory]"
    return
fi

# -ue   Undocumented element warnings
# -ti   Docs title
# -o    Output format
# -it   Ignored tags
# -i    Ignored files
# -f    Source files
# -d    Source directories
# -t    Target directory

sourceFiles="Module.php,Module/DbDependent.php,Agent.php,Analyst.php,BcMath,php,Cache.php,Client.php,Core.php,DbHelper.php,Hashmark.php,Module.php,Partition.php,Test.php,Util.php,Test/Case.php"
sourceDirs="Agent,Config,Cron,Module,Sql,Test"

phpdoc \
-ue \
-ti Hashmark \
-o HTML:frames:DOM/earthli \
-it @group,@test \
-i Test/Analyst/BasicDecimal/Data/provider.php,Test/error_log \
-f $sourceFiles \
-d $sourceDirs \
-t $1
