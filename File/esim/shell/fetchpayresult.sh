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
#对账主机待接收下载文件根目录
SRCDIR=/root/esim/paycenter/balance_result
#支付中心SFTP服务器待下载目录
DESDIR=/unibss/users/pcsftp01/fileinterface/output/balance_result/esim_platform
#待下载文件名
NOWDATE=$(date +%Y%m%d)
FILE=${NOWDATE}_PCS_00000015_0001.REQ


lftp -u ${USER},${PASSWORD} sftp://${IP}:${PORT} <<EOF
cd ${DESDIR}/
lcd ${SRCDIR}
get ${FILE}
by
EOF
