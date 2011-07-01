-- Replace /*_*/ with the proper prefix
-- Replace /*$wgDBTableOptions*/ with the correct options

CREATE TABLE IF NOT EXISTS /*_*/projects (    

    p_project         varchar(63) not null,
    -- project name

    p_timestamp       binary(14) not null,
    -- last time project data was updated

    p_wikipage        varchar(255),
    -- homepage on the wiki for this project

    p_parent          varchar(63),
    -- parent project (for task forces)

    p_shortname       varchar(255),
    -- display name in headers 

    p_count           int unsigned default 0,
    -- how many pages are assessed in project 

    p_qcount          int unsigned default 0,
    -- how many pages have quality assessments in the project

    p_icount          int unsigned default 0,
    -- how many pages have importance assessments in the project 

    p_scope    int unsigned not null default 0,
    -- the project's "scope points", used to compute selection scores

    primary key (p_project)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/p_project ON /*_*/projects (p_project);
