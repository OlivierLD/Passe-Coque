import requests
import json

#
# An invocati0on example of the weather.php REST API, in Python.
#

api_url: str = "http://passe-coque.com/tech.and.nav/weather.php/weather.php"

payload = { "type": "PRMSL", "value": 996.1 }
headers =  { "Content-Type":"application/json" }
response = requests.post(api_url, data=json.dumps(payload), headers=headers)
returned = response.json()
print(returned)

status = response.status_code
print(status)
