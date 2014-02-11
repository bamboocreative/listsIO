set :stages,        %w(production staging)
set :default_stage, "staging"
set :stage_dir,     "app/config"
require 'capistrano/ext/multistage'

set :application, "lists.io"
set :domain,      "lists.io"
set :deploy_to,   "/var/www/vhosts/#{domain}"
set :deploy_via,  :rsync_with_remote_cache
set :app_path,    "app"
set :web_path,    "web"

set(:password){ Capistrano::CLI.password_prompt("Type your SSH password for user \"#{user}\": ") }

set :writable_dirs,       ["app/cache", "app/logs"]
set :webserver_user,      "apache"
set :permission_method,   :acl
set :use_set_permissions, true

set :repository,  "https://github.com/bamboocreative/listsIO.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor", app_path + "/sessions"]

set :use_composer, true
set :update_vendors, true

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL