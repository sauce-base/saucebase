export default [
    {
        name: 'billing.setup',
        testMatch: /billing\.setup\.ts/,
        dependencies: ['database.setup'],
    },
];
