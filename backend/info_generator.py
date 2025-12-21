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

# Global maps for ID lookups
MANUFACTURERS_MAP = {}
CAR_TYPES_MAP = {}
CAR_MODELS_LIST = [] # List of dicts
USER_IDS = []


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
        exit()

def execute_query(conn, sql, params=None):
    """Executes a prepared statement and returns success status."""
    cursor = conn.cursor()
    try:
        cursor.execute(sql, params or ())
        conn.commit()
        return cursor.lastrowid if 'INSERT' in sql.upper() else True
    except mysql.connector.Error as err:
        print(f"MySQL Error: {err} | Query: {sql}")
        return False
    finally:
        cursor.close()

def generate_id_hash(data):
    """Generates a SHA-512 hash for ID compromise strategy."""
    return hashlib.sha512(data.encode('utf-8')).hexdigest()

def generate_vin():
    """Generates a random VIN (17 alphanumeric)"""
    return fake.bothify(text='?#?????##########', letters='ABCDEFGHIJKLMNOPQRSTUVWXYZ')

# =======================================================
#                   FAKER SCRIPT START
# =======================================================

print("Starting Python database population...\n")

conn = get_db_connection()

# --- Data Arrays for Consistency ---

manufacturers_data = ['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes', 'Nissan', 'Hyundai', 'Kia']
car_models_by_manufacturer = {
    'Toyota': ['Corolla', 'Camry', 'Hilux', 'Fortuner'],
    'Honda': ['Civic', 'CR-V', 'Pilot'],
    'Ford': ['Focus', 'F-150', 'Explorer'],
    'BMW': ['3 Series', 'X5', 'i4'],
    'Mercedes': ['C-Class', 'GLC', 'E-Class'],
    'Nissan': ['Sentra', 'Rogue', 'Titan'],
    'Hyundai': ['Elantra', 'Tucson'],
    'Kia': ['Forte', 'Telluride']
}
car_types_data = ['Sedan', 'SUV', 'Pickup Truck', 'Hatchback', 'Van', 'Coupe', 'Electric']
colors = ['White', 'Black', 'Silver', 'Red', 'Blue', 'Grey']


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
sql = "INSERT INTO CarModels (manufacturerId, model_name, year) VALUES (%s, %s, %s)"
for year in range(2018, 2025):
    for man_name, models in car_models_by_manufacturer.items():
        man_id = MANUFACTURERS_MAP[man_name]
        for model_name in models:
            if random.random() < 0.7: continue # Generate a random subset of models
            
            last_id = execute_query(conn, sql, (man_id, model_name, year))
            if last_id:
                CAR_MODELS_LIST.append({
                    'modelId': last_id,
                    'manufacturerId': man_id,
                    'model_name': model_name,
                    'type_name': random.choice(car_types_data) # Temp assign for Car generation
                })
print(f"   Generated {len(CAR_MODELS_LIST)} distinct car models.")


# 4. RentalRates
print("4. Populating RentalRates...")
sql = "INSERT INTO RentalRates (typeId, daily_rate, effective_date) VALUES (%s, %s, %s)"
rates = {
    'Sedan': 50.00, 'SUV': 75.00, 'Pickup Truck': 90.00, 'Hatchback': 45.00, 
    'Van': 80.00, 'Coupe': 60.00, 'Electric': 70.00
}
today = date.today().isoformat()
for type_name, rate in rates.items():
    type_id = CAR_TYPES_MAP[type_name]
    execute_query(conn, sql, (type_id, rate, today))


# 5. Users and CustomerDetails (50 Records)
print("5. Populating 50 Users and CustomerDetails...")
user_sql = "INSERT INTO Users (username, email, phone_number, password, type_of_user) VALUES (%s, %s, %s, %s, %s)"
customer_sql = "INSERT INTO CustomerDetails (userId, physical_address, id_document_hash, next_of_kin_contact) VALUES (%s, %s, %s, %s)"

for i in range(50):
    # User Data
    username = fake.user_name() + str(i)
    email = fake.email()
    phone_number = fake.numerify(text='555########')
    hashed_password = hashlib.sha256(f"password{i}".encode('utf-8')).hexdigest() # Simple hash for faker
    user_type = 'A' if i < 2 else 'N'
    
    # Insert User
    user_id = execute_query(conn, user_sql, (username, email, phone_number, hashed_password, user_type))
    if user_id:
        USER_IDS.append(user_id)

        # Customer Data
        address = fake.address().replace('\n', ', ')
        # Use phone number as a seed for the hash to simulate a unique ID
        id_hash = generate_id_hash(phone_number + str(user_id))
        kin_contact = fake.numerify(text='555########')
        
        # Insert CustomerDetails
        execute_query(conn, customer_sql, (user_id, address, id_hash, kin_contact))


# 6. Cars (50 Records)
print("6. Populating 50 Cars...")
cars_sql = "INSERT INTO Cars (VIN, plate_number, manufacturerId, modelId, typeId, colour, num_seats, tow_capacity_kg, is_available) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
VINS = [] # Store VINs for Rentals table

for _ in range(50):
    vin = generate_vin()
    VINS.append(vin)
    
    plate = fake.license_plate()
    
    # Pick a random CarModel
    model_data = random.choice(CAR_MODELS_LIST)
    man_id = model_data['manufacturerId']
    model_id = model_data['modelId']
    
    # Lookup typeId based on the model's assigned type
    type_id = CAR_TYPES_MAP[model_data['type_name']]

    color = random.choice(colors)
    seats = random.choice([4, 5, 7])
    
    # Tow capacity logic
    tow_capacity = random.randint(1000, 3500) if model_data['type_name'] in ['Pickup Truck', 'SUV'] else None
    
    is_available = random.choice([True] * 9 + [False] * 1) # 90% chance available

    execute_query(conn, cars_sql, (
        vin, plate, man_id, model_id, type_id, color, seats, tow_capacity, is_available
    ))


# =======================================================
# 7. Rentals (40 Records) - REMADE
# =======================================================

from decimal import Decimal # Ensure this is imported at the top of the script

print("7. Populating 40 Rentals (Corrected Financials)...")
rental_sql = "INSERT INTO Rentals (userId, VIN, start_date, end_date, return_date, daily_rate_used, expected_total_cost, deposit_amount, total_paid, payment_method, rental_status) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"

rates_data = {}
cursor = conn.cursor()
# Fetch rates (which are returned as Decimal objects by mysql.connector)
cursor.execute("SELECT rr.daily_rate, ct.typeId FROM RentalRates rr JOIN CarTypes ct ON rr.typeId = ct.typeId")
for rate, type_id in cursor.fetchall():
    rates_data[type_id] = rate
cursor.close()

for i in range(40):
    user_id = random.choice(USER_IDS)
    vin = random.choice(VINS)
    
    # Get the car's typeId to find the rate
    cursor = conn.cursor()
    cursor.execute("SELECT typeId FROM Cars WHERE VIN = %s", (vin,))
    car_type_id = cursor.fetchone()[0]
    cursor.close()
    
    # Ensure daily_rate is a Decimal object, which prevents the TypeError.
    daily_rate = rates_data.get(car_type_id, Decimal('50.00')) 
    
    # Dates
    start_date_obj = fake.date_object() + timedelta(days=random.randint(0, 60))
    rental_days = random.randint(3, 14)
    end_date_obj = start_date_obj + timedelta(days=rental_days)
    
    start_date = start_date_obj.isoformat()
    end_date = end_date_obj.isoformat()
    
    # Status and Payments
    status = random.choice(['BOOKED', 'PICKED_UP', 'RETURNED'])
    
    # Calculation using only Decimal types
    expected_total = daily_rate * Decimal(rental_days)
    
    # Deposit must also be Decimal
    deposit_amount = Decimal('50.00') if status != 'BOOKED' else Decimal('0.00')
    
    if status == 'BOOKED':
        total_paid = Decimal('0.00')
        return_date = None
        payment_method = 'N/A'
    else:
        # This is the corrected line: performing addition between two Decimal objects
        total_paid = expected_total + deposit_amount 
        payment_method = random.choice(['Cash', 'Speed Point'])
        
        if status == 'RETURNED':
            return_date_obj = end_date_obj + timedelta(days=random.randint(-1, 2))
            return_date = return_date_obj.isoformat()
        else:
            return_date = None
    
    execute_query(conn, rental_sql, (
        user_id, vin, start_date, end_date, return_date, float(daily_rate), float(expected_total), 
        float(deposit_amount), float(total_paid), payment_method, status
    ))

print("\nRentals loop successfully updated and tested for type errors. Ready to run! 🎉")

conn.close()
print("\nPython database population complete! 🎉")