import { expect, type Locator, type Page } from '@playwright/test';

export class SettingsBillingPage {
    readonly page: Page;
    readonly subscriptionSection: Locator;
    readonly planName: Locator;
    readonly cancelButton: Locator;
    readonly resumeButton: Locator;
    readonly cancelDialog: Locator;
    readonly cancelDialogConfirm: Locator;
    readonly cancelDialogCancel: Locator;
    readonly noSubscription: Locator;

    constructor(page: Page) {
        this.page = page;
        this.subscriptionSection = page.getByTestId('subscription-section');
        this.planName = page.getByTestId('plan-name');
        this.cancelButton = page.getByTestId('cancel-button');
        this.resumeButton = page.getByTestId('resume-button');
        this.cancelDialog = page.getByTestId('confirm-dialog');
        this.cancelDialogConfirm = page.getByTestId('confirm-dialog-confirm');
        this.cancelDialogCancel = page.getByTestId('confirm-dialog-cancel');
        this.noSubscription = page.getByTestId('no-subscription');
    }

    async goto() {
        await this.page.goto('/settings/billing');
    }

    async expectNoSubscription() {
        await expect(this.noSubscription).toBeVisible();
    }

    async expectPlanName(name: string) {
        await expect(this.planName).toHaveText(name);
    }

    async openCancelDialog() {
        await this.cancelButton.click();
    }

    async expectCancelDialogVisible() {
        await expect(this.cancelDialogConfirm).toBeVisible();
        await expect(this.cancelDialogCancel).toBeVisible();
    }

    async closeCancelDialog() {
        await this.cancelDialogCancel.click();
    }
}
