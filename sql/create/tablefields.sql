CREATE TABLE "tablefields" (
	"xid"	INTEGER NOT NULL,
	"tblname"	TEXT,
	"colname"	TEXT,
	"native_type"	TEXT,
	"pdo_type"	TEXT,
	"len"	TEXT,
	"controlid"	INTEGER,
	"controlname"	TEXT,
    "createdon" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("xid")
)