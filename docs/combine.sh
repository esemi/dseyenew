#!/usr/bin/env bash

LIBS_DIR="../www/js/libs/";
RESULT_FILENAME="combine.js";
FILES=('jQ.min.js' 'jQui.min.js' 'jquery.dimensions.min.js' 'jquery.tooltip.min.js' 'highstock.js');

touch $LIBS_DIR/$RESULT_FILENAME;
cat /dev/null > $LIBS_DIR/$RESULT_FILENAME;

for f in "${FILES[@]}"
do
	echo $f;
	echo "/*********** start delimeter **********/" >> $LIBS_DIR/$RESULT_FILENAME;
	cat $LIBS_DIR/$f >> $LIBS_DIR/$RESULT_FILENAME;
	echo "/*********** end delimeter **********/" >> $LIBS_DIR/$RESULT_FILENAME;
done;