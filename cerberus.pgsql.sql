DROP TABLE IF EXISTS "auth";
DROP TYPE IF EXISTS "auth_enum_authlevel";
CREATE TYPE "auth_enum_authlevel" as enum('none','user','admin');
CREATE TABLE "auth" (
	"id" serial NOT NULL ,
	"network" varchar(255) NOT NULL ,
	"authlevel" auth_enum_authlevel NOT NULL ,
	"authname" varchar(255) NOT NULL ,
	PRIMARY KEY ("id")
);
SELECT setval('"auth_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "bot";
CREATE TABLE "bot" (
	"id" serial NOT NULL ,
	"server_id" integer ,
	"pid" integer NOT NULL ,
	"nick" varchar(255) ,
	"start" timestamp with time zone ,
	"stop" timestamp with time zone ,
	"ping" timestamp with time zone ,
	PRIMARY KEY ("id")
);
SELECT setval('"bot_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "channel";
CREATE TABLE "channel" (
	"id" serial NOT NULL ,
	"bot_id" integer NOT NULL ,
	"channel" varchar(255) NOT NULL ,
	"topic" text ,
	PRIMARY KEY ("id")
);
SELECT setval('"channel_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "channel_user";
DROP TYPE IF EXISTS "channel_user_enum_mode";
CREATE TYPE "channel_user_enum_mode" as enum('','+','@');
CREATE TABLE "channel_user" (
	"id" serial NOT NULL ,
	"bot_id" integer NOT NULL ,
	"channel" varchar(255) NOT NULL ,
	"username" varchar(255) NOT NULL ,
	"mode" channel_user_enum_mode NOT NULL ,
	PRIMARY KEY ("id")
);
SELECT setval('"channel_user_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "channellist";
CREATE TABLE "channellist" (
	"id" serial NOT NULL ,
	"network" varchar(200) NOT NULL ,
	"channel" varchar(200) NOT NULL ,
	"usercount" integer NOT NULL ,
	"topic" text NOT NULL ,
	"time" timestamp with time zone NOT NULL ,
	PRIMARY KEY ("id")
);
SELECT setval('"channellist_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "log";
DROP TYPE IF EXISTS "log_enum_direction";
CREATE TYPE "log_enum_direction" as enum('in','out');
CREATE TABLE "log" (
	"id" serial NOT NULL ,
	"bot_id" integer NOT NULL ,
	"network" varchar(200) NOT NULL ,
	"nick" varchar(200) NOT NULL ,
	"host" varchar(200) NOT NULL ,
	"command" varchar(200) NOT NULL ,
	"rest" varchar(200) NOT NULL ,
	"text" text NOT NULL ,
	"irc" text NOT NULL ,
	"time" timestamp with time zone NOT NULL ,
	"direction" log_enum_direction NOT NULL ,
	PRIMARY KEY ("id")
);
SELECT setval('"log_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "network";
CREATE TABLE "network" (
	"id" serial NOT NULL ,
	"network" varchar(255) NOT NULL ,
	PRIMARY KEY ("id")
);
DROP INDEX IF EXISTS "network_network";
CREATE UNIQUE INDEX "network_network" ON "network" ("network");
SELECT setval('"network_id_seq"', 3, true);
INSERT INTO "network" ("id","network") VALUES 
('1','freenode'),
('2','quakenet');

DROP TABLE IF EXISTS "preform";
CREATE TABLE "preform" (
	"id" serial NOT NULL ,
	"network" varchar(200) NOT NULL ,
	"text" text NOT NULL ,
	"priority" integer NOT NULL ,
	PRIMARY KEY ("id")
);
SELECT setval('"preform_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "send";
CREATE TABLE "send" (
	"id" serial NOT NULL ,
	"bot_id" integer NOT NULL ,
	"text" text NOT NULL ,
	PRIMARY KEY ("id")
);
SELECT setval('"send_id_seq"', 1, true);
;

DROP TABLE IF EXISTS "server";
CREATE TABLE "server" (
	"id" serial NOT NULL ,
	"network_id" integer NOT NULL ,
	"servername" varchar(255) NOT NULL ,
	"server" varchar(255) NOT NULL ,
	"port" varchar(5) NOT NULL ,
	PRIMARY KEY ("id")
);
DROP INDEX IF EXISTS "server_server";
CREATE UNIQUE INDEX "server_server" ON "server" ("server");
SELECT setval('"server_id_seq"', 3, true);
INSERT INTO "server" ("id","network_id","servername","server","port") VALUES 
('1','1','Random server','chat.freenode.net','6667'),
('2','2','Random server','irc.quakenet.org','6667');

DROP TABLE IF EXISTS "web";
CREATE TABLE "web" (
	"id" serial NOT NULL ,
	"email" varchar(255) NOT NULL ,
	"password" varchar(255) NOT NULL ,
	"sid" varchar(255) ,
	PRIMARY KEY ("id")
);
SELECT setval('"web_id_seq"', 1, true);
;
