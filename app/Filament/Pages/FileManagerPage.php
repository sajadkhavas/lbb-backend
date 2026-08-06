<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-code-bracket';
    protected static ?string $navigationLabel = 'ویرایش فایل‌ها';
    protected static ?string $navigationGroup = 'سیستم';
    protected static ?int    $navigationSort  = 10;
    protected static ?string $title           = 'ویرایش فایل‌های سایت';
    protected static string  $view            = 'filament.pages.file-manager';

    public string $fileContent      = '';
    public string $selectedFile     = '';
    public string $selectedFileName = '';
    public string $selectedFileExt  = '';
    public string $newFileName      = '';
    public string $newFileFolder    = '';
    public string $searchQuery      = '';
    public string $buildOutput      = '';
    public string $gitStatus        = '';
    public string $commitMessage    = '';
    public bool   $showNewFileModal = false;
    public bool   $showCommitModal  = false;
    public bool   $editorReady      = false;

    public function mount(): void
    {
        $this->refreshGitStatus();
    }

    protected function getAllowedBasePaths(): array
    {
        return [
            'فرانت - صفحات'       => base_path('src/pages'),
            'فرانت - کامپوننت‌ها'  => base_path('src/components'),
            'فرانت - API'         => base_path('src/api'),
            'فرانت - Hooks'       => base_path('src/hooks'),
            'فرانت - Contexts'    => base_path('src/contexts'),
            'بکند - Routes'       => base_path('routes'),
            'بکند - Config'       => base_path('config'),
            'بکند - Lang'         => base_path('lang'),
        ];
    }

    protected function isPathAllowed(string $path): bool
    {
        $real = realpath($path);
        if (!$real) {
            $dir = realpath(dirname($path));
            if (!$dir) return false;
            $real = $dir . DIRECTORY_SEPARATOR . basename($path);
        }
        foreach ($this->getAllowedBasePaths() as $base) {
            $realBase = realpath($base);
            if ($realBase && str_starts_with($real, $realBase)) return true;
        }
        return false;
    }

    public function getFiles(string $path): array
    {
        if (!is_dir($path)) return [];
        $files = [];
        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            $files[] = [
                'name'     => $file,
                'path'     => $fullPath,
                'isDir'    => is_dir($fullPath),
                'ext'      => pathinfo($file, PATHINFO_EXTENSION),
                'size'     => is_file($fullPath) ? round(filesize($fullPath) / 1024, 1) . ' KB' : '',
                'modified' => date('Y/m/d H:i', filemtime($fullPath)),
            ];
        }
        usort($files, fn($a, $b) => $b['isDir'] <=> $a['isDir']);
        return $files;
    }

    public function openFile(string $path): void
    {
        if (!$this->isPathAllowed($path) || !is_file($path)) {
            Notification::make()->title('دسترسی مجاز نیست')->danger()->send();
            return;
        }
        $this->selectedFile     = $path;
        $this->selectedFileName = basename($path);
        $this->selectedFileExt  = pathinfo($path, PATHINFO_EXTENSION);
        $this->fileContent      = file_get_contents($path);
        $this->refreshGitStatus();

        // dispatch به browser event - روش صحیح در Livewire 3
        $this->dispatch('file-opened',
            content: $this->fileContent,
            ext: $this->selectedFileExt,
            filename: $this->selectedFileName,
        );
    }

    public function editorContentChanged(string $content): void
    {
        $this->fileContent = $content;
    }

    public function saveFile(): void
    {
        if (empty($this->selectedFile) || !$this->isPathAllowed($this->selectedFile)) {
            Notification::make()->title('خطا: فایلی انتخاب نشده')->danger()->send();
            return;
        }
        file_put_contents($this->selectedFile, $this->fileContent);
        $this->refreshGitStatus();
        Notification::make()->title('✅ ذخیره شد')->body($this->selectedFileName)->success()->send();
    }

    public function saveAndBuild(): void
    {
        $this->saveFile();
        $this->runBuild();
    }

    public function runBuild(): void
    {
        $root = base_path();
        $cmd  = PHP_OS_FAMILY === 'Windows'
            ? "cd /d \"{$root}\" && npm run build 2>&1"
            : "cd \"{$root}\" && npm run build 2>&1";

        $output   = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);
        $this->buildOutput = implode("\n", $output);

        if ($exitCode === 0) {
            Notification::make()->title('✅ Build موفق!')->success()->send();
        } else {
            Notification::make()->title('❌ Build ناموفق')->body('لاگ را بررسی کنید')->danger()->send();
        }
    }

    public function downloadFile(): StreamedResponse|null
    {
        if (empty($this->selectedFile) || !$this->isPathAllowed($this->selectedFile)) return null;
        return response()->streamDownload(
            fn() => print(file_get_contents($this->selectedFile)),
            basename($this->selectedFile)
        );
    }

    public function createFile(): void
    {
        if (empty($this->newFileName) || empty($this->newFileFolder)) {
            Notification::make()->title('نام فایل و پوشه را وارد کنید')->warning()->send();
            return;
        }
        $basePaths = $this->getAllowedBasePaths();
        if (!isset($basePaths[$this->newFileFolder])) {
            Notification::make()->title('پوشه نامعتبر')->danger()->send();
            return;
        }
        $newPath = $basePaths[$this->newFileFolder] . DIRECTORY_SEPARATOR . $this->newFileName;
        if (!$this->isPathAllowed($newPath)) {
            Notification::make()->title('دسترسی مجاز نیست')->danger()->send();
            return;
        }
        if (file_exists($newPath)) {
            Notification::make()->title('فایل از قبل وجود دارد')->warning()->send();
            return;
        }
        $ext  = pathinfo($this->newFileName, PATHINFO_EXTENSION);
        $name = pathinfo($this->newFileName, PATHINFO_FILENAME);
        $template = match($ext) {
            'tsx' => "import React from 'react';\n\nconst {$name} = () => {\n  return (\n    <div>\n      {/* محتوا */}\n    </div>\n  );\n};\n\nexport default {$name};\n",
            'ts'  => "// {$this->newFileName}\n\nexport {};\n",
            'php' => "<?php\n\n",
            default => '',
        };
        file_put_contents($newPath, $template);
        $this->selectedFile     = $newPath;
        $this->selectedFileName = basename($newPath);
        $this->selectedFileExt  = $ext;
        $this->fileContent      = $template;
        $this->newFileName      = '';
        $this->showNewFileModal = false;

        $this->dispatch('file-opened', content: $template, ext: $ext, filename: $this->selectedFileName);
        Notification::make()->title('✅ فایل ساخته شد')->body(basename($newPath))->success()->send();
    }

    public function deleteFile(): void
    {
        if (empty($this->selectedFile) || !$this->isPathAllowed($this->selectedFile)) {
            Notification::make()->title('دسترسی مجاز نیست')->danger()->send();
            return;
        }
        $name = basename($this->selectedFile);
        unlink($this->selectedFile);
        $this->selectedFile     = '';
        $this->selectedFileName = '';
        $this->fileContent      = '';
        $this->gitStatus        = '';
        $this->dispatch('file-closed');
        Notification::make()->title('🗑 حذف شد')->body($name)->success()->send();
    }

    public function refreshGitStatus(): void
    {
        $root = base_path();
        $cmd  = PHP_OS_FAMILY === 'Windows'
            ? "cd /d \"{$root}\" && git status --short 2>&1"
            : "cd \"{$root}\" && git status --short 2>&1";
        $output = [];
        exec($cmd, $output);
        $this->gitStatus = implode("\n", $output);
    }

    public function gitCommit(): void
    {
        if (empty($this->commitMessage)) {
            Notification::make()->title('پیام commit را وارد کنید')->warning()->send();
            return;
        }
        $root = base_path();
        $msg  = escapeshellarg($this->commitMessage);
        $cmd  = PHP_OS_FAMILY === 'Windows'
            ? "cd /d \"{$root}\" && git add -A && git commit -m {$msg} 2>&1"
            : "cd \"{$root}\" && git add -A && git commit -m {$msg} 2>&1";
        $output   = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);
        $this->showCommitModal = false;
        $this->commitMessage   = '';
        $this->refreshGitStatus();
        if ($exitCode === 0) {
            Notification::make()->title('✅ Commit موفق!')->body(implode("\n", $output))->success()->send();
        } else {
            Notification::make()->title('❌ Commit ناموفق')->body(implode("\n", $output))->danger()->send();
        }
    }

    public function clearBuildOutput(): void
    {
        $this->buildOutput = '';
    }

    public function getViewData(): array
    {
        return ['basePaths' => $this->getAllowedBasePaths()];
    }
}
