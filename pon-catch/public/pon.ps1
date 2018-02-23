$picPath = "C:\pon\empty.zip"
$uri = "http://ptsv2.com/t/9o97s-1518792132"

Invoke-WebRequest -uri $uri -Method Post -Infile $picPath -ContentType 'application/zip'

