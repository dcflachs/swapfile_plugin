#!/bin/bash
[ -z "$OUTPUT_FOLDER" ] && echo "Output Folder not set" && exit 1
tmpdir=/tmp/tmp.$(( $RANDOM * 19318203981230 + 40 ))
version=$(date +"%Y.%m.%d")$1

mkdir -p $tmpdir/usr/local/emhttp/plugins/swapfile

cp -RT /mnt/source/Resources/usr/local/emhttp/plugins/swapfile/ $tmpdir/usr/local/emhttp/plugins/swapfile/

cd $tmpdir

chmod -R +x $tmpdir/usr/local/emhttp/plugins/swapfile/scripts/
chmod -R +x $tmpdir/usr/local/emhttp/plugins/swapfile/php/
chmod -R +x $tmpdir/usr/local/emhttp/plugins/swapfile/event/

makepkg -l y -c y $OUTPUT_FOLDER/swapfile-package-${version}.txz

cd /

rm -rf $tmpdir

echo "MD5:"

md5sum $OUTPUT_FOLDER/swapfile-package-${version}.txz