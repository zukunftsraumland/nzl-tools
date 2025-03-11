# regiosuisse Tools

Welcome to the regiosuisse Tools Collection!

This repository houses a variety of tools provided by [regiosuisse](https://regiosuisse.ch). 
While the issue tracker is currently disabled, we encourage you to reach out to [our team](https://regiosuisse.ch/regiosuisse-team) with any questions or feedback you may have.

## Installation

    docker compose build
    docker compose up -d
    
    docker exec -it nzl-tools_php bash -c "composer install"
    docker exec -it nzl-tools_php bash -c "php bin/console doctrine:migrations:migrate --no-interaction"
    docker exec -it nzl-tools_php bash -c "yarn install && yarn build"
    docker exec -it nzl-tools_php bash -c "php bin/console app:user:create"

## Configuration

Available configuration options can be set via `dotenv` and can be overwritten via `.env.local` or directly in the `.env` file. 
Most options are optional, however the following options must be modified in order to run in a production environment:

| Variable            | Description                         |
|---------------------|-------------------------------------|
| `APP_ENV`           | _No description_                    |
| `APP_SECRET`        | _No description_                    |
| `DATABASE_URL`      | Database credentials                |
| `MAILER_DSN`        | Mail server credentials             |
| `MAILER_FROM`       | Mail sender address                 |
| `HOST`              | Host address of production instance |
| `FRONTEND_HOST`     | Host address of primary frontend    |
| `CORS_ALLOW_ORIGIN` | _No description_                    |
| `MAPBOX_API_TOKEN`  | API Token for Mapbox access         |

## Documentation

Currently, we don't have user interface documentation available. However, you can access various technical documents here:

| Name             | URL                                                                                                          |
|------------------|--------------------------------------------------------------------------------------------------------------|
| OpenAPI (UI)     | [http://localhost/api/documentation](http://localhost/api/documentation)                                     |
| OpenAPI          | [http://localhost/api/documentation.json](http://localhost/api/documentation.json)                           |
| Projects Embed   | [http://localhost/embed/projects/documentation.html](http://localhost/embed/projects/documentation.html)     |
| Events Embed     | [http://localhost/embed/events/documentation.html](http://localhost/embed/events/documentation.html)         |
| Jobs Embed       | [http://localhost/embed/jobs/documentation.html](http://localhost/embed/jobs/documentation.html)             |
| Educations Embed | [http://localhost/embed/educations/documentation.html](http://localhost/embed/educations/documentation.html) |
| Regions Embed    | [http://localhost/embed/regions/documentation.html](http://localhost/embed/regions/documentation.html)       |

## Misc

### Authenticate with CHMOS

To authenticate with CHMOS, follow these steps:

1. Obtain your `*.pem` certificate.
2. Place the certificate in the `config/secrets/chmos` directory.

You're now ready to import projects from CHMOS.

### Update GeoJSON

```
cd ./config/gis
ogr2ogr -f GeoJSON -s_srs *.prj -t_srs EPSG:4326 cities.json *.shp
```

### Import cities XLSX

```
php bin/console app:import:cities \
   --municipal-number-column=B \
   --name-column=C \
   --state-column=E \
   *.xlsx
```

### Import regions XLSX

```
php bin/console app:import:regions \
   --municipal-number-column=B \
   --state-column=E \
   --name-columns=E \
   --name-columns=H \
   --name-columns=J \
   --name-columns=K \
   --name-columns=L \
   --name-columns=M \
   --name-columns=N \
   --name-columns=O \
   --name-columns=P \
   --name-columns=Q \
   --type-mapping=cantonal \
   --type-mapping=nrp \
   --type-mapping=ris \
   --type-mapping=ris \
   --type-mapping=ris \
   --type-mapping=ris \
   --type-mapping=ris \
   --type-mapping=ris \
   --type-mapping=intercantonal \
   --type-mapping=energy \
   --remove-orphans \
   *.xlsx
```

### Project Import Functionality

The Project Import feature allows you to import projects from Excel files into the system. Here's how it works:

#### Supported Importers

The system supports multiple import formats:

- **Standard Importer**: The default importer for Excel files with a specific column structure.
- **Case Study Importer**: A specialized importer for case study projects.
- **Legacy Importer**: For importing projects from legacy data formats.

#### Import Process

1. **Upload Phase**:
   - Upload an Excel file through the web interface.
   - The system stores the file and prepares it for preview.
   - No images are downloaded at this stage.

2. **Preview Phase**:
   - The system reads the Excel file and displays a preview of the data.
   - You can review the data for errors or warnings before proceeding.
   - The preview shows validation results for each row.

3. **Import Phase**:
   - Select an LE Period for the imported projects.
   - Start the import process.
   - The system processes each row in the Excel file:
     - Creates project entities
     - Downloads images and files from URLs specified in the Excel file
     - Associates projects with the selected LE Period
   - A progress indicator shows the status of the import.

#### Image Handling

- Images are not downloaded during the upload or preview phases.
- During the actual import process, the system:
  - Reads image URLs from the Excel file
  - Downloads the images from the specified URLs
  - Processes and stores them with the project
  - Handles various image formats and validates them

#### Running Imports via Command Line

You can also process pending imports via the command line:

```
php bin/console app:process-project-imports
```

This is useful for scheduling imports or processing large imports in the background.

#### Excel File Structure

The Standard Importer expects specific columns in the Excel file. Key columns include:

- Project title and description
- Start and end dates
- Contact information
- File attachments (with filename and URL pairs)
- Geographic information
- Categorization data

Refer to the template files or contact the team for detailed Excel structure requirements.
