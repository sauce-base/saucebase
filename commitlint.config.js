export default {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'type-enum': [
            2,
            'always',
            [
                'feat', // A new feature
                'fix', // A bug fix
                'docs', // Documentation only changes
                'style', // Changes that do not affect the meaning of the code (white-space, formatting, etc)
                'refactor', // A code change that neither fixes a bug nor adds a feature
                'perf', // A code change that improves performance
                'test', // Adding missing tests or correcting existing tests
                'chore', // Changes to the build process or auxiliary tools and libraries
                'ci', // Changes to CI configuration files and scripts
                'build', // Changes that affect the build system or external dependencies
                'revert', // Reverts a previous commit
            ],
        ],
    },
};
