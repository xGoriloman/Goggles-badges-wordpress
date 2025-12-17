const fs = require('fs');
const path = require('path');

class FileScanner {
    constructor(outputFile = 'scan_results.txt') {
        this.outputFile = outputFile;
        this.results = [];
    }

    scanDirectory(dirPath) {
        try {
            const items = fs.readdirSync(dirPath);
            
            for (const item of items) {
                const fullPath = path.join(dirPath, item);
                const stat = fs.statSync(fullPath);
                
                if (stat.isDirectory()) {
                    // Пропускаем системные папки
                    if (!['node_modules', '.git'].includes(item)) {
                        this.scanDirectory(fullPath);
                    }
                } else if (stat.isFile()) {
                    this.processFile(fullPath);
                }
            }
        } catch (error) {
            console.error(`Ошибка: ${dirPath}`);
        }
    }

    processFile(filePath) {
        try {
            console.log(`Сканирую: ${filePath}`);
            const content = fs.readFileSync(filePath, 'utf8');
            
            this.results.push({
                path: filePath,
                content: content
            });
            
        } catch (error) {
            this.results.push({
                path: filePath,
                content: `[ОШИБКА ЧТЕНИЯ: ${error.message}]`
            });
        }
    }

    writeResults() {
        let outputContent = '';
        
        for (const result of this.results) {
            outputContent += `путь до файла\n${result.path}\n`;
            outputContent += `содержимое файла\n${result.content}\n`;
            outputContent += '\n' + '='.repeat(50) + '\n\n';
        }
        
        fs.writeFileSync(this.outputFile, outputContent, 'utf8');
        console.log(`✅ Готово! Результаты в файле: ${this.outputFile}`);
    }

    scan(startPath) {
        console.log(`Начинаю сканирование: ${startPath}`);
        
        if (!fs.existsSync(startPath)) {
            console.error(`❌ Путь не существует: ${startPath}`);
            return;
        }
        
        const stat = fs.statSync(startPath);
        
        if (stat.isDirectory()) {
            this.scanDirectory(startPath);
        } else if (stat.isFile()) {
            this.processFile(startPath);
        }
        
        this.writeResults();
    }
}

// Запуск
const scanner = new FileScanner('результат.txt');
const scanPath = process.argv[2] || '.';
scanner.scan(scanPath);
