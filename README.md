# mopedauto

Docker Container, welcher eine Tasmota Steckdose
zeitgesteuert ansteuern kann.

Z.B. um ein Elektroauto, bei welchen sich der maximale Ladezustand nicht begrenzen lässt.

D.h. ein Webinterface, bei dem sich der Ist-Ladestand
einstellen lässt, und der am Ende des Ladevorganges erreichte
Sollzustand einstellen lässt.

Anleitung :

(Repostioory clonen - eh klar. :-)
.config.php <- anpassen
chmod +x bauen.sh start.sh

./bauen.sh

Die start.sh anpassen. Da drinnen muss man die gewünschte IP und den Port anpassen.

unter /raid/mopedauto/scripts - sollte es eine "nachricht.sh" geben.
Notfalls halt eine leere Datei anlegen mit :
"touch /raid/mopedauto/scripts/nachricht.sh && chmod +x /raid/mopedauto/scripts/nachricht.sh".

./start.sh
