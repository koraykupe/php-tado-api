# UNOFFICIAL TADO API CLIENT LIBRARY FOR PHP
Create `.env` file in the root by copying `.env.default` and fill in your credentials.

Username and password are the credentials you use when logging in to the Tado app.

You can find your `client_secret` on https://my.tado.com/webapp/env.js 


### USAGE
```
$tado = new TadoClient();
$generalInfo = $tado->getGeneralInfo();  // You can find your home Id(s) here
$homeDetails = $tado->getHome($homeId); // You can find your zone (room) Id(s) here
$tado->setTemperature(123456, 3, false); // Turn off heating for zone 3
$tado->setTemperature(123456, 3, true); // Turn on heating for zone 3
$tado->setTemperature(123456, 3, true, 22); // Set zone 3 to 22 Celcius degrees
$tado->setTemperature(123456, 2, true, 23, 45); // Set zone 2 to 23 Celcius degrees for 45 minutes
```
It is recommended to use a secure way when creating your URLs.
e.g: IP based restriction, unique hashed URL (home id, zone id, etc)

### ROADMAP
- Fahrenheit support
- Add tests

