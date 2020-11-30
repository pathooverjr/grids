CREATE TABLE "js_functions_twig" (
	"xid"	INTEGER NOT NULL,
	"name"	TEXT,
	"app"	TEXT,
	"twigBlock"	TEXT,
	"type"	TEXT,
	"event"	TEXT,
	"nodeIDName"	TEXT,
	"actionFunction"	TEXT,
	"data"	TEXT,
    "createdon" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("xid")
)