Monorepo Tools
======================================================================

The directory contains helper scripts to split, build and maintain subpackages within the [Presentator monorepo](https://github.com/presentator/presentator).

Most of the scripts are based on [Shopsys Monorepo Tools](https://github.com/shopsys/monorepo-tools).


## Quick reference

This is just a short description and usage of all the tools in the package.
For detailed information go to the scripts themselves and read the comments.

### [monorepo_build](./monorepo_build)

Build monorepo from specified remotes. The remotes must be already added to your repository and fetched.

Usage: `monorepo_build <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...`

### [monorepo_split](./monorepo_split)

Split monorepo built by `monorepo_build` and push all `master` branches along with all tags into specified remotes.

Usage: `monorepo_split <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...`

### [monorepo_add](./monorepo_add)

Add repositories to an existing monorepo from specified remotes. The remotes must be already added to your repository and fetched. Only master branch will be added from each repo.

Usage: `monorepo_add <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...`

### [rewrite_history_into](./rewrite_history_into)

Rewrite git history (even tags) so that all filepaths are in a specific subdirectory.

Usage: `rewrite_history_into <subdirectory> [<rev-list-args>]`

### [rewrite_history_from](./rewrite_history_from)

Rewrite git history (even tags) so that only commits that made changes in a subdirectory are kept and rewrite all filepaths as if it was root.

Usage: `rewrite_history_from <subdirectory> [<rev-list-args>]`

### [original_refs_restore](./original_refs_restore)

Restore original git history after rewrite.

Usage: `original_refs_restore`

### [original_refs_wipe](./original_refs_wipe)

Wipe original git history after rewrite.

Usage: `original_refs_wipe`

### [load_branches_from_remote](./load_branches_from_remote)

Delete all local branches and create all non-remote-tracking branches of a specified remote.

Usage: `load_branches_from_remote <remote-name>`

### [tag_refs_backup](./tag_refs_backup)

Backup tag refs into `refs/original-tags/`

Usage: `tag_refs_backup`

### [tag_refs_move_to_original](./tag_refs_move_to_original)

Move tag refs from `refs/original-tags/` into `refs/original/`

Usage: `tag_refs_move_to_original`
