#!/bin/bash
#SFTP配置信息
#IP
IP=134.78.9.65
#端口
PORT=22
#用户名
USER=pcsftp01
#密码
PASSWORD=Pw#0809@pcs
#待上传文件根目录
SRCDIR=/root/esim/paycenter/service_balance
#SFTP目录
DESDIR=/unibss/users/pcsftp01/fileinterface/input/service_balance/esim_platform
#待上传文件名
LASTDATE=$(date -d last-day +%Y%m%d)
FILE=${LASTDATE}_00000015_PCS_0001.REQ


lftp -u ${USER},${PASSWORD} sftp://${IP}:${PORT} <<EOF
cd ${DESDIR}/
lcd ${SRCDIR}
put ${FILE}
by
EOF

