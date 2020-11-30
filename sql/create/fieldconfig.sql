CREATE TABLE "fieldconfig" (
	"xid"	INTEGER NOT NULL,
	"fieldid"	INTEGER,
	"controldetailid"	INTEGER,
	"createdon" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("xid")
)