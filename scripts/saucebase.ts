#!/usr/bin/env tsx

import * as p from '@clack/prompts';
import { execSync, spawn } from 'child_process';
import pc from 'picocolors';

interface TaskDef {
    name: string;
    desc: string;
}

function loadTasks(): TaskDef[] {
    try {
        const json = execSync('task --list-all --json', {
            encoding: 'utf-8',
            stdio: ['pipe', 'pipe', 'pipe'],
        });
        const data = JSON.parse(json) as {
            tasks: { name: string; desc: string }[];
        };
        return data.tasks.map(({ name, desc }) => ({ name, desc }));
    } catch {
        p.log.error(
            'Failed to load tasks from Taskfile. Is the task binary installed?',
        );
        process.exit(1);
    }
}

const tasks = loadTasks();

// Build flat lookup of all tasks
const allTasks = new Map<string, TaskDef>();
for (const task of tasks) {
    allTasks.set(task.name, task);
}

function runTask(taskName: string, args: string[]): Promise<number> {
    return new Promise((resolve) => {
        const child = spawn('task', [taskName, ...args], {
            stdio: 'inherit',
            env: { ...process.env },
        });

        child.on('close', (code) => resolve(code ?? 1));
        child.on('error', (err) => {
            if ((err as NodeJS.ErrnoException).code === 'ENOENT') {
                p.log.error(
                    'task binary not found. Install: brew install go-task',
                );
            } else {
                p.log.error(`Failed to run task: ${err.message}`);
            }
            resolve(1);
        });
    });
}

async function interactive(): Promise<void> {
    const options = tasks.map((task) => ({
        value: task.name,
        label: `${pc.cyan(task.name.padEnd(12))} ${pc.dim(task.desc)}`,
    }));

    const selected = await p.select({
        message: 'What would you like to do?',
        options,
    });

    if (p.isCancel(selected)) {
        p.cancel('Cancelled.');
        process.exit(0);
    }

    p.log.step(`Running ${pc.cyan(selected as string)}...`);
    const code = await runTask(selected as string, []);
    if (code === 0) {
        p.outro(pc.green('Done!'));
    } else {
        p.outro(pc.red(`Exited with code ${code}`));
        process.exit(code);
    }
}

async function direct(taskName: string, args: string[]): Promise<void> {
    if (!allTasks.has(taskName)) {
        // Still attempt — Taskfile may have tasks not listed in the menu
        p.log.warn(`Unknown task ${pc.cyan(taskName)}, attempting anyway...`);
    } else {
        const task = allTasks.get(taskName)!;
        p.log.step(`${pc.cyan(taskName)} ${pc.dim(`— ${task.desc}`)}`);
    }

    const code = await runTask(taskName, args);
    if (code === 0) {
        p.outro(pc.green('Done!'));
    } else {
        process.exit(code);
    }
}

// ── Main ──────────────────────────────────────────────────────
const [taskName, ...rest] = process.argv.slice(2);

// Split rest into task args (after --)
const dashIndex = rest.indexOf('--');
const taskArgs = dashIndex >= 0 ? ['--', ...rest.slice(dashIndex + 1)] : rest;

p.intro(pc.bgCyan(pc.black(' Saucebase ')));

if (taskName) {
    direct(taskName, taskArgs);
} else {
    interactive();
}
