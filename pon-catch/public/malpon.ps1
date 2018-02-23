$uri = "http://ptsv2.com/t/9o97s-1518792132"

Invoke-WebRequest -uri $uri -Method Post -Body "id=2014&filename=0609.csv&format=json"
