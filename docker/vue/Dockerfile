FROM node:18-bullseye-slim

WORKDIR /app

EXPOSE 5173

# npm install runs at startup (not build) because:
# - The source code is bind-mounted from host
# - node_modules is a named volume (persists between restarts)
# - First run installs deps, subsequent runs are instant
CMD ["sh", "-c", "npm install && npm run dev"]
