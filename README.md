# Traccar-Webhook
Have traccar trigger an external webhook for events, can be used with CM.com TTS gateway.

# Pre Requirements 
Modify traccar.xml by adding:
```
   <!-- event forwarding -->
    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.url'>http://webservice_to_do_your_business/</entry>
```

# Thanks to:
```
https://webhook.site/#!/309c4ced-890f-440d-9b7b-8a8a563788a3
https://jpmens.net/2018/09/14/position-and-event-forwarding-from-traccar/
```
