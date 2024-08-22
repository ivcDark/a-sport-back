# Указываем базовый образ Node.js
FROM node:16-alpine

# Устанавливаем рабочую директорию
WORKDIR /usr/src/app

# Копируем файлы проекта
COPY . .

# Устанавливаем зависимости
RUN npm install

# Собираем проект
RUN npm run build

# Устанавливаем сервер для раздачи статики
RUN npm install -g http-server

# Экспонируем порт
EXPOSE 8080

# Запускаем сервер
CMD ["http-server", "dist/your-project-name", "-p", "8080"]
