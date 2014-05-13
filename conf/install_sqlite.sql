create table #prefix#course_category (
	id integer primary key,
	owner int not null,
	title char(72) not null,
	sorting int not null
);

create index #prefix#course_category_owner on #prefix#course_category (owner, sorting);

create table #prefix#course_course (
	id integer primary key,
	title char(72) not null,
	thumb char(128),
	summary char(255) not null,
	created datetime not null,
	owner int not null,
	category int not null,
	sorting int not null,
	availability int not null,
	price float not null,
	status int not null,
	has_glossary int not null default 0,
	instructor int not null default 0
);

create index #prefix#course_course_owner_category on #prefix#course_course (owner, category, sorting, status);

create table #prefix#course_page (
	id integer primary key,
	title char(72) not null,
	course int not null,
	sorting int not null
);

create index #prefix#course_page_course on #prefix#course_page (course, sorting);

create table #prefix#course_item (
	id integer primary key,
	title char(192) not null,
	page int not null,
	sorting int not null,
	type int not null,
	content text not null,
	answer char(128) not null default '',
	course int not null
);

create index #prefix#course_item_page on #prefix#course_item (page, sorting);
create index #prefix#course_item_course on #prefix#course_item (course, type);

create table #prefix#course_learner (
	user integer not null,
	course integer not null,
	ts datetime not null,
	primary key (user, course)
);

create index #prefix#course_learner_ts on #prefix#course_learner (ts);

create table #prefix#course_data (
	id integer primary key,
	course int not null,
	user int not null,
	item int not null,
	status int not null,
	correct int not null,
	ts datetime not null,
	answer text not null,
	feedback text not null
);

create index #prefix#course_data_user on #prefix#course_data (user, ts);
create index #prefix#course_data_course_user on #prefix#course_data (course, user);
create index #prefix#course_data_item_user on #prefix#course_data (item, user);
