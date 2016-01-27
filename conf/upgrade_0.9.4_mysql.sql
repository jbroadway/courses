alter table #prefix#courses_learner add column score tinyint not null default -1;
alter table #prefix#courses_learner add column passed tinyint not null default -1;
