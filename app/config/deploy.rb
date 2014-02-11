set :stages,        %w(production staging)
set :default_stage, "staging"
set :stage_dir,     "app/config"
require 'capistrano/ext/multistage'

set :application, "lists.io"
set :domain,      "72.47.211.211"
set :deploy_to,   "/var/www/vhosts/lists.io"
set :deploy_via,  :rsync_with_remote_cache
set :app_path,    "app"
set :web_path,    "web"

set :user, "deploy"
default_run_options[:pty] = true

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
set :dump_assetic_assets, true

set :use_composer, true
set :update_vendors, true

role :web,        "lists.io"            # Your HTTP server, Apache/etc
role :app,        "lists.io/web", :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL