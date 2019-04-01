#!/bin/bash
#SFTP配置信息
#IP
IP=134.78.65.157
#端口
PORT=22
#用户名
USER=ftp_esim
#密码
PASSWORD=#EDC4rfv
#待上传文件根目录
SRCDIR=/root/esim/recharge
#SFTP目录
DESDIR=/reconciliation
#待上传文件名
LASTDATE=$(date -d last-day +%Y%m%d)
FILE=8361.${LASTDATE}


lftp -u ${USER},${PASSWORD} sftp://${IP}:${PORT} <<EOF
cd ${DESDIR}/
lcd ${SRCDIR}
put ${FILE}
by
EOF
