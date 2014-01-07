#!/usr/bin/env bash

LIBS_DIR="../www/js/libs/";
RESULT_FILENAME="combine.js";
FILES=('jQ.min.js' 'jQui.min.js' 'jquery.dimensions.min.js' 'jquery.tooltip.min.js' 'highcharts.js');

touch $LIBS_DIR/$RESULT_FILENAME;
cat /dev/null > $LIBS_DIR/$RESULT_FILENAME;

for f in "${FILES[@]}"
do
	echo $f;
	cat $LIBS_DIR/$f >> $LIBS_DIR/$RESULT_FILENAME;
	echo "/*********** delimeter **********/" >> $LIBS_DIR/$RESULT_FILENAME;
done;