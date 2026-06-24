# Gemini Unauthenticated RPC Gateway PoC (Captcha Solver Demo)

This repository demonstrates an architectural vulnerability allowing unauthenticated access to Gemini's production infrastructure. As a practical Proof of Concept (PoC), this implementation turns the undocumented backend into a free, high-speed automated captcha-solving engine.

## Research Credits
* **Lead Developer:** Adnan Awan ( gbgleaks@gmail.com )
* **Project Suite:** Kaptch.com
* **Location:** Karachi, Pakistan

Demo online : https://www.kaptch.com

Source code : "index.php" already uploaded you can download

## Technical Context (Why this matters)
Google infrastructure uses extensive anti-bot filtering, yet this undocumented endpoint allows direct programmatic inference loops without any API tokens or account handshakes. This PoC proves that developers can feed raw text/logic descriptions of captcha challenges directly to the endpoint and retrieve accurate text solutions programmatically, shifting heavy computing costs onto Google unauthorized.

## Disclosure Info
Disclosed to Google VRP under Issue #523965922. Since the Google AI VRP Team closed the report claiming this is "Intended Behavior," this architecture study is published to show how enterprise-level processing boundaries can be bypassed.
