#/bin/sh

make
rm xsmall.idx
rm xsmall.tbl
./bruinbase < testXsmall.sql
