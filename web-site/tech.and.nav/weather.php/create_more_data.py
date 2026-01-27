import requests
import json

#
# An invocati0on example of the weather.php REST API, in Python.
#

api_url: str = "http://passe-coque.com/tech.and.nav/weather.php/weather.php"

prmsl: float = 1001.3  # hPa
at: float = 15.6       # °C
rh: float = 82.0       # %
dp: float = 12.3       # °C
ah: float = 10.5       # g/m3

verbose: bool = True

payload = [
    {"type": "PRMSL", "value": prmsl},
    {"type": "AT", "value": at},
    {"type": "RH", "value": rh},
    {"type": "DEW-P", "value": dp},
    {"type": "AH", "value": ah}
]
headers = {"Content-Type": "application/json"}
for oneline in payload:
    response = requests.post(api_url, data=json.dumps(oneline), headers=headers)
    status = response.status_code
    returned = response.json()
    if (verbose):
        print(f"Status: {status}")
        print(returned)
# That's it.
