import mysql.connector
from faker import Faker
import random
import hashlib
from datetime import date, timedelta
from decimal import Decimal

# =======================================================
#                   DATABASE CONFIG
# =======================================================
DB_USERNAME = "brid"
DB_PASSWORD = "chirp"
SERVERNAME = "localhost"
DB_NAME = "Mizoragi_Car_Rental_DB"

# Initialize Faker
fake = Faker()

# Global maps for ID lookups and data storage
MANUFACTURERS_MAP = {}
CAR_TYPES_MAP = {}
CAR_MODELS_LIST = [] # List of dicts (for Car and Rate generation)
USER_IDS = []
VINS = [] # Store VINs for Rentals table
MODEL_RATES = {} # NEW: To store {modelId: daily_rate} for use in Rentals table generation

# =======================================================
#                   HELPER FUNCTIONS
# =======================================================

def get_db_connection():
    """Establishes and returns a database connection."""
    try:
        conn = mysql.connector.connect(
            host=SERVERNAME,
            user=DB_USERNAME,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        return conn
    except mysql.connector.Error as err:
        print(f"Database Connection Failed: {err}")
        exit(1)

def execute_query(conn, sql, params=None):
    """Executes a prepared statement and returns success status/last ID."""
    cursor = conn.cursor()
    try:
        cursor.execute(sql, params or ())
        conn.commit()
        return cursor.lastrowid if 'INSERT' in sql.upper() else True
    except mysql.connector.Error as err:
        print(f"MySQL Error: {err} | Query: {sql} | Params: {params}")
        return False
    finally:
        cursor.close()

def generate_id_hash(data):
    """Generates a SHA-512 hash for ID compromise strategy."""
    return hashlib.sha512(data.encode('utf-8')).hexdigest()

def generate_vin():
    """Generates a random VIN (17 alphanumeric)"""
    return fake.bothify(text='?#?????##########', letters='ABCDEFGHIJKLMNOPQRSTUVWXYZ').upper()

# =======================================================
#                   FAKER SCRIPT START
# =======================================================

print("Starting Python database population...\n")
conn = get_db_connection()

# --- Data Arrays for Consistency ---
manufacturers_data = ['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes', 'Nissan', 'Hyundai', 'Kia']
car_models_by_manufacturer = {
    'Toyota': [('Corolla', 'Sedan'), ('Camry', 'Sedan'), ('Hilux', 'Pickup Truck'), ('Fortuner', 'SUV')],
    'Honda': [('Civic', 'Hatchback'), ('CR-V', 'SUV'), ('Pilot', 'SUV')],
    'Ford': [('Focus', 'Hatchback'), ('F-150', 'Pickup Truck'), ('Explorer', 'SUV')],
    'BMW': [('3 Series', 'Sedan'), ('X5', 'SUV'), ('i4', 'Electric')],
    'Mercedes': [('C-Class', 'Sedan'), ('GLC', 'SUV'), ('E-Class', 'Sedan')],
    'Nissan': [('Sentra', 'Sedan'), ('Rogue', 'SUV'), ('Titan', 'Pickup Truck')],
    'Hyundai': [('Elantra', 'Sedan'), ('Tucson', 'SUV')],
    'Kia': [('Forte', 'Sedan'), ('Telluride', 'SUV')]
}
car_types_data = ['Sedan', 'SUV', 'Pickup Truck', 'Hatchback', 'Van', 'Coupe', 'Electric']
colors = ['White', 'Black', 'Silver', 'Red', 'Blue', 'Grey', 'Green']
rental_payment_methods = ['Cash', 'Speed Point', 'EFT', 'Mobile Pay']

# Rate Modifiers (for new model-specific rates)
RATE_MODIFIERS = {
    'Sedan': 50.00, 'SUV': 75.00, 'Pickup Truck': 90.00, 'Hatchback': 45.00, 
    'Van': 80.00, 'Coupe': 60.00, 'Electric': 70.00
}

# 1. Manufacturers
print("1. Populating Manufacturers...")
sql = "INSERT INTO Manufacturers (name) VALUES (%s)"
for name in manufacturers_data:
    last_id = execute_query(conn, sql, (name,))
    if last_id:
        MANUFACTURERS_MAP[name] = last_id


# 2. CarTypes
print("2. Populating CarTypes...")
sql = "INSERT INTO CarTypes (type_name, description) VALUES (%s, %s)"
for type_name in car_types_data:
    description = f"{type_name} category for rental."
    last_id = execute_query(conn, sql, (type_name, description))
    if last_id:
        CAR_TYPES_MAP[type_name] = last_id


# 3. CarModels
print("3. Populating CarModels...")
sql = "INSERT INTO CarModels (manufacturerId, model_name, year, num_seats, tow_capacity_kg) VALUES (%s, %s, %s, %s, %s)"

for year in range(2018, 2025):
    for man_name, models in car_models_by_manufacturer.items():
        man_id = MANUFACTURERS_MAP[man_name]
        for model_name, type_name in models:
            if random.random() < 0.3: continue 
            
            # Determine characteristics based on type
            num_seats = random.choice([4, 5])
            tow_capacity = None
            if type_name in ['Pickup Truck', 'SUV']:
                num_seats = random.choice([5, 7]) if type_name == 'SUV' else 2
                tow_capacity = round(random.uniform(1500, 4000), 2)
            elif type_name == 'Van':
                num_seats = 12
            
            last_id = execute_query(conn, sql, (man_id, model_name, year, num_seats, tow_capacity))
            
            if last_id:
                CAR_MODELS_LIST.append({
                    'modelId': last_id,
                    'manufacturerId': man_id,
                    'model_name': model_name,
                    'type_name': type_name, 
                    'typeId': CAR_TYPES_MAP[type_name], # Store typeId for Car table generation
                    'num_seats': num_seats,
                    'tow_capacity_kg': tow_capacity
                })
print(f"   Generated {len(CAR_MODELS_LIST)} distinct car models.")


# 4. RentalRates (NEW: Linked to ModelId)
print("4. Populating RentalRates (by Model)...")
sql = "INSERT INTO RentalRates (modelID, daily_rate, effective_date) VALUES (%s, %s, %s)"

today = date.today().isoformat()
for model in CAR_MODELS_LIST:
    # Get base rate for the car's type
    base_rate = Decimal(str(RATE_MODIFIERS[model['type_name']]))
    
    # Add small random variation based on year (newer cars are slightly more expensive)
    random_adjustment = Decimal(str(random.uniform(-5.00, 10.00)))
    final_rate = base_rate + random_adjustment
    
    # Ensure rate is positive and rounded
    final_rate = round(max(Decimal('30.00'), final_rate), 2) 

    execute_query(conn, sql, (
        model['modelId'], 
        float(final_rate), # Pass as float/Decimal for MySQL
        today
    ))
    
    # Store rate for quick lookup in Rentals generation
    MODEL_RATES[model['modelId']] = final_rate 


# 5. Users and CustomerDetails (70 Records: 50 Normal, 2 Admin, 18 Others)
print("5. Populating 70 Users and CustomerDetails...")
user_sql = "INSERT INTO Users (username, email, phone_number, password, type_of_user) VALUES (%s, %s, %s, %s, %s)"
customer_sql = "INSERT INTO CustomerDetails (userId, physical_address, id_document_hash, next_of_kin_contact) VALUES (%s, %s, %s, %s)"

for i in range(70):
    username = fake.unique.user_name() + str(i)
    email = fake.unique.email()
    phone_number = fake.unique.numerify(text='555########')
    # Using SHA256 as a placeholder for a proper password hash function (PHP password_hash)
    hashed_password = hashlib.sha256(f"password{i}".encode('utf-8')).hexdigest()
    user_type = 'A' if i < 2 else 'N'
    
    user_id = execute_query(conn, user_sql, (username, email, phone_number, hashed_password, user_type))
    
    if user_id:
        USER_IDS.append(user_id)
        
        # Only create CustomerDetails for Normal users
        if user_type == 'N':
            address = fake.address().replace('\n', ', ')
            id_hash = generate_id_hash(fake.ssn())
            kin_contact = fake.numerify(text='555########')
            
            execute_query(conn, customer_sql, (user_id, address, id_hash, kin_contact))


# 6. Cars (100 Records)
print("6. Populating 100 Cars...")
cars_sql = "INSERT INTO Cars (VIN, plate_number, manufacturerId, modelId, typeId, colour, is_available) VALUES (%s, %s, %s, %s, %s, %s, %s)"
car_images_sql = "INSERT INTO CarImages (VIN, file_path, is_main_photo, caption) VALUES (%s, %s, %s, %s)"

for i in range(100):
    vin = generate_vin()
    VINS.append(vin)
    
    plate = fake.unique.license_plate()
    
    model_data = random.choice(CAR_MODELS_LIST)
    man_id = model_data['manufacturerId']
    model_id = model_data['modelId']
    type_id = model_data['typeId'] # Fetched from the data stored in step 3

    color = random.choice(colors)
    is_available = random.choice([True] * 8 + [False] * 2)

    execute_query(conn, cars_sql, (
        vin, plate, man_id, model_id, type_id, color, is_available
    ))
    
    # CarImages (3 per car, 1 main)
    for img_index in range(1, 4):
        is_main = (img_index == 1)
        file_path = f"/cars/images/{vin}_{img_index}.jpg"
        caption = f"{model_data['model_name']} - {['Front', 'Side', 'Interior'][img_index-1]}"
        execute_query(conn, car_images_sql, (vin, file_path, is_main, caption))


# 7. Rentals (100 Records - NEW Rate Lookup)
print("7. Populating 100 Rentals...")
rental_sql = "INSERT INTO Rentals (userId, VIN, start_date, end_date, return_date, daily_rate_used, expected_total_cost, deposit_amount, total_paid, payment_method, rental_status) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"

# Map VIN to ModelId for rate lookup
vin_to_modelid = {}
cursor = conn.cursor()
cursor.execute("SELECT VIN, modelId FROM Cars")
for vin, model_id in cursor.fetchall():
    vin_to_modelid[vin] = model_id
cursor.close()

for i in range(100):
    user_id = random.choice(USER_IDS)
    vin = random.choice(VINS)
    
    car_model_id = vin_to_modelid.get(vin)
    # Get rate from the MODEL_RATES map, based on ModelId
    daily_rate = MODEL_RATES.get(car_model_id, Decimal('50.00')) 
    
    # Dates
    start_date_obj = fake.date_object() + timedelta(days=random.randint(-30, 90))
    rental_days = random.randint(3, 14)
    end_date_obj = start_date_obj + timedelta(days=rental_days)
    
    start_date = start_date_obj.isoformat()
    end_date = end_date_obj.isoformat()
    
    # Status and Payments
    status = random.choice(['BOOKED'] * 3 + ['PICKED_UP'] * 2 + ['RETURNED'] * 4 + ['CANCELLED'] * 1 + ['COMPLETE'] * 1)
    
    # Calculation using Decimal
    expected_total_cost = daily_rate * Decimal(rental_days)
    
    # Deposit amount
    deposit_amount = Decimal('250.00') if status != 'CANCELLED' else Decimal('0.00')
    total_paid = Decimal('0.00')
    return_date = None
    payment_method = None
    
    if status in ['RETURNED', 'COMPLETE']:
        total_paid = expected_total_cost + deposit_amount
        payment_method = random.choice(rental_payment_methods)
        return_date_obj = end_date_obj + timedelta(days=random.randint(-2, 3))
        return_date = return_date_obj.isoformat()
    elif status == 'PICKED_UP':
        total_paid = expected_total_cost + deposit_amount
        payment_method = random.choice(rental_payment_methods)
    elif status == 'BOOKED':
        total_paid = Decimal('50.00') if random.random() < 0.7 else Decimal('0.00') 
        payment_method = 'EFT' if total_paid > 0 else None

    # MySQL expects None for NULL, and float/Decimal for DECIMAL types
    execute_query(conn, rental_sql, (
        user_id, vin, start_date, end_date, return_date, float(daily_rate), 
        float(expected_total_cost), float(deposit_amount), float(total_paid), 
        payment_method, status
    ))

print("\nPython database population complete! Data is consistent with DDL. 🎉")
conn.close()