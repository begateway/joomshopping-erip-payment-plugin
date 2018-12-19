all:
	if [[ -e plg_system_joomshoppingerip.zip ]]; then rm plg_system_joomshoppingerip.zip; fi
	cd plg_system_joomshoppingerip &&	zip -r ../plg_system_joomshoppingerip.zip * -x "*/test/*" -x "*/.git/*" -x "*/examples/*"
