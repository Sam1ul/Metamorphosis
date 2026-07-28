from gtts import gTTS
import os
import time
import pyautogui
import difflib
import hashlib
import subprocess

from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.pipeline import make_pipeline


# =========================
# SAFETY
# =========================
pyautogui.FAILSAFE = True


# =========================
# GREETINGS
# =========================

GREETINGS = {
    # =========================
    # Basic Greetings
    # =========================
    #"hi": "Hello! I am your Metamorphosis assistant.",
    "hello": "Hi there! Ready to learn cybersecurity?",
    "hey": "Hey! Ask me about web vulnerabilities.",
    "hiya": "Hiya! Let's explore cybersecurity together.",
    "heya": "Heya! Ready for ethical hacking practice?",
    "yo": "Yo! Ready for some cybersecurity challenges?",
    "sup": "What's up! Need help with security concepts?",
    "wassup": "Wassup! Let's secure the digital world.",
    "hola": "Hola! Welcome to cybersecurity learning.",
    "namaste": "Namaste! Ready to dive into cyber defense?",
    "bonjour": "Bonjour! Let's learn ethical hacking.",
    "ciao": "Ciao! Stay safe in cyberspace.",
    "salam": "Salam! How can I help with cybersecurity today?",
    "assalamu alaikum": "Wa alaikum assalam! Welcome to Metamorphosis.",
    "good day": "Good day! Ready to improve your cyber skills?",
    "greetings": "Greetings! Welcome to your cybersecurity assistant.",
    "salutations": "Salutations! Let's explore digital security.",
    "welcome": "Welcome! Cybersecurity starts here.",

    # =========================
    # Time-Based Greetings
    # =========================
    "good morning": "Good morning! Let's secure your applications.",
    "morning": "Morning! Time to learn cybersecurity.",
    "good afternoon": "Good afternoon! Ready for cyber training?",
    "afternoon": "Afternoon! Stay alert and stay secure.",
    "good evening": "Good evening! Ready for security learning?",
    "evening": "Evening! Let's talk cybersecurity.",
    "good night": "Good night! Keep your systems protected.",
    "night": "Night! Remember to use strong passwords.",
    "midnight": "Burning the midnight oil with cybersecurity?",
    "early morning": "Early mornings are great for hacking practice!",

    # =========================
    # Friendly Chat
    # =========================
    "how are you": "I'm functioning perfectly and ready to help!",
    "how are you doing": "Doing great and securing cyberspace!",
    "how's it going": "Everything is secure and running smoothly!",
    "what's up": "Protecting the digital world! What about you?",
    "howdy": "Howdy partner! Ready for ethical hacking?",
    "nice to meet you": "Nice to meet you too! Let's learn security.",
    "long time no see": "Welcome back! Ready for more cyber learning?",
    "missed you": "Glad you're back to continue cybersecurity training!",
    "are you there": "Always here and ready to assist.",
    "can you help me": "Of course! Ask me anything about cybersecurity.",

    # =========================
    # Farewells
    # =========================
    "bye": "Goodbye! Stay safe online.",
    "goodbye": "Farewell! Keep practicing cybersecurity.",
    "see you": "See you later! Stay secure.",
    "take care": "Take care and secure your systems!",
    "later": "Catch you later, cybersecurity learner!",
    "farewell": "Farewell! Keep defending the digital world.",
    "see ya": "See ya! Don't forget to update your passwords.",
    "gn": "Good night! Stay cyber safe.",
    "good night bye": "Sleep well and keep your data protected.",
    "talk to you later": "Looking forward to our next cybersecurity session.",

    # =========================
    # Thank You Responses
    # =========================
    "thanks": "You're welcome! Happy hacking ethically.",
    "thank you": "Glad to help! Keep learning cybersecurity.",
    "thx": "No problem! Stay secure.",
    "ty": "You're very welcome!",
    "thanks a lot": "Always happy to help with cyber knowledge.",
    "many thanks": "My pleasure! Stay safe online.",
    "appreciate it": "Glad I could help!",
    "awesome thanks": "You're awesome too! Keep learning.",
    "cool thanks": "Anytime! Cybersecurity is fun.",

    # # =========================
    # # Cybersecurity Themed
    # # =========================
    "hack": "Ethical hacking only! How can I assist you?",
    "hacker": "Hackers can protect systems when acting ethically.",
    "security": "Cybersecurity is the key to a safer digital world.",
    "cyber": "Cyber defense starts with knowledge!",
    "pentest": "Penetration testing is an exciting skill to learn!",
    "ctf": "Capture The Flag challenges are great for practice!",
    "exploit": "Understanding exploits helps build stronger defenses.",
    "malware": "Always analyze malware safely in sandboxed environments.",
    "phishing": "Be cautious of suspicious emails and fake websites.",
    "ransomware": "Regular backups are essential against ransomware.",
    #"sql injection": "Input validation prevents SQL injection attacks.",
    #"xss": "Cross-site scripting can be prevented with output encoding.",
    #"csrf": "CSRF tokens help secure web applications.",
    "bug bounty": "Bug bounty hunting is a great way to learn.",
    "firewall": "Firewalls help monitor and filter network traffic.",
    "encryption": "Encryption protects sensitive information.",
    "osint": "OSINT is powerful for ethical investigations.",
    "linux": "Linux is widely used in cybersecurity environments.",
    "kali linux": "Kali Linux is packed with security testing tools.",
    "wireshark": "Wireshark is excellent for packet analysis.",
    "metasploit": "Metasploit is useful for penetration testing practice.",
    "nmap": "Nmap is a powerful network scanning tool.",

    # =========================
    # Motivational
    # =========================
    "motivate me": "Every cybersecurity expert started as a beginner.",
    "i am learning": "That's awesome! Consistency builds strong skills.",
    "i want to hack": "Learn ethical hacking responsibly and legally.",
    "teach me": "I'm ready to guide you through cybersecurity topics.",
    "help": "Sure! Ask me about networking, hacking, or web security.",
    "i am bored": "Try solving a cybersecurity CTF challenge!",
    "give me challenge": "Ready for a hacking challenge? Let's begin.",
    "inspire me": "Cybersecurity skills can change the future.",

    # =========================
    # Fun / Casual
    # =========================
    "tell me a joke": "Why do hackers love Linux? Because they can't Windows properly!",
    "lol": "Cybersecurity can be fun too 😄",
    "haha": "Glad you're enjoying the learning experience!",
    "cool": "Cybersecurity is definitely cool.",
    "nice": "Awesome! Keep exploring cyber concepts.",
    "amazing": "Cybersecurity is full of amazing discoveries.",
    "wow": "The digital world is fascinating, isn't it?",
    "great": "Great! Let's continue learning.",
    "awesome": "Awesome! You're doing fantastic.",
    "epic": "Epic cybersecurity journey ahead!",
}


def detect_greeting(q):
    for g in GREETINGS:
        if g in q:
            return g
    return None


def handle_greeting(greet):
    msg = GREETINGS[greet]

    print("\n🤖", msg)
    speak(msg)


# =========================
# AUDIO SYSTEM
# =========================

def speak(text):

    try:
        key = hashlib.md5(text.encode()).hexdigest()
        filename = f"voice_{key}.mp3"

        # generate voice once
        if not os.path.exists(filename):
            tts = gTTS(text=text, lang="en")
            tts.save(filename)

        # play silently
        os.system(
            f"ffplay -nodisp -autoexit -loglevel quiet {filename}"
        )

    except Exception as e:
        print("[TTS ERROR]", e)


# =========================
# ACTION ENGINE
# =========================

def execute_action(action):

    if action is None:
        return

    action_type, value = action

    if action_type == "press":
        pyautogui.press(value)

    elif action_type == "write":
        pyautogui.write(value, interval=0.03)

    elif action_type == "hotkey":
        pyautogui.hotkey(*value)


# =========================
# OWASP KNOWLEDGE BASE
# =========================

OWASP = {

    "sql injection": {

        "definition":
            "Sql Injection refers to user input is executed as SQL commands on a database.",

        # app startup wait
        "startup_delay": 10,

        "steps": [
            {
                "text": "Lets see how the sql injection makes a website vulnerable",
                "action": None,
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/login.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },

            #browser opened now..

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("write", "admin' OR '1'='1"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 7
            },

            {
                "text": "Here we can see that we can access other user's account using sql injection.",
                "action": None,
                "delay": 1
            },

            {
                "text": "Lets see a diagram to see what is actually happening",
                "action": ("hotkey", ['ctrl','alt','t']),
                "delay": 10
            },

            {
                "text": "",
                "action": ("write", "open /home/kathersis/Documents/MAI/pic/problem/sqli.png"),
                "delay": 5
            },
            {
                "text": "This diagram demonstrates a cyberattack called SQL Injection. An attacker enters a malicious string (admin' or '1'='1) into the username field of a login form to manipulate the database query. By adding '1'='1', they create a tautology,which is a statement that is always true. This trick forces the database to evaluate the login request as successful, regardless of whether the password is correct. Consequently, the attacker can bypass authentication and gain unauthorized access to the admin account.",
                "action": ("press", "enter"),
                "delay": 7
            },

            {
                "text": "",
                "action": ("hotkey", ['ctrl','q']),
                "delay": 5
            },

            {
                "text": "",
                "action": ("write", "open /home/kathersis/Documents/MAI/pic/anti-problem/a-sqli.png"),
                "delay": 5
            },

            {
                "text": "Now this diagram demonstrates how sql injection can be prevented simply using parameterized query.Parameterized queries separate the SQL command logic from the user-provided data.They use placeholders instead of inserting input directly into the query string.The database compiles the SQL template first, then binds the user input as a literal value later.Because of this separation, the database treats malicious input as simple text rather than executable code.This completely prevents SQL injection because strings like ' OR '1'='1 are searched for as text, not run as commands.",
                "action": ("press", "enter"),
                "delay": 7
            },

            {
                "text": "",
                "action": ("hotkey", ['ctrl','q']),
                "delay": 5
            },

            {
                "text": "",
                "action": ("hotkey", ['ctrl','d']),
                "delay": 5
            },





            #==================================================
            #secured sqli
            {
                "text": "Then let's see how the secured website from sql injection look like",
                "action": None,
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("hotkey", ["shift","tab"]),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("hotkey", ["shift","tab"]),
                "delay": 0.5
            },

            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/secured_bank_demo/login.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },

            #browser opened now..

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("write", "admin' OR '1'='1"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 7
            },

            {
                "text": "Here we can see that we can not access other user's account using sql injection.",
                "action": None,
                "delay": 1
            },

            {
                "text": "Thus we have seen how sql injection works and how counter measure sets the website strong.There is more example in the website, Try yourself",
                "action": ("hotkey", ["ctrl", "q"]),
                "delay": 1
            }
        ]
    },

    "xss": {

        "definition":
            "Cross site script or XSS happens when attackers inject malicious javascripts into web pages.",

        "startup_delay": 10,

        "steps": [

            {
                "text": "Lets see how the xss makes a website vulnerable",
                "action": None,
                "delay": 1
            },
            

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
 
            
            {
                "text": "",
                "action": (
                    "write",
                    "admin' OR '1'='1"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
             
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("hotkey", ["shift","tab"]),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("hotkey", ["shift","tab"]),
                "delay": 0.5
            },
            
            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/feedback.php"
                ),
                "delay": 0.5
            },              {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            },     
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },            {
                "text": "",
                "action": (
                    "write",
                    ' <script> alert("XSS Vulnerability") </script>'
                ),
                "delay": 0.5
            },            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },             {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            },             {
                "text": "Now every time the page loads injected javasript will run",
                "action": ("press", "enter"),
                "delay": 7
            },            {
                "text": "",
                "action": ("hotkey", ["ctrl","q"]),
                "delay": 0.5
            },
                  

           


 
            

 
            
        ]
    },
    "csrf": {

        "definition":
            "Attackers inject malicious scripts into web pages.",

        "startup_delay": 2,

        "steps": [

            {
                "text": "Lets see how the CSRF makes a website vulnerable",
                "action": None,
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/login.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },

            #browser opened now..

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("write", "admin' OR '1'='1"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 3
            },
            
            # ==============logged in now 

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("hotkey", ["shift","tab"]),
                "delay": 1
            },
            {
                "text": "",
                "action": ("hotkey", ["shift","tab"]),
                "delay": 1
            },
            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/transfer.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            
            
            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/dashboard.php"
                ),
                "delay": 0.5
            },              {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            },  
            {
                "text": "As you can see that the amount of money has been transferred without users consent",
                "action": None,
                "delay": 3
            },
            {
                "text": "",
                "action": ("hotkey", ["ctrl","q"]),
                "delay": 0.5
            },
            

        ]
    },
    "cryptographic failure": {

        "definition":
            "A cryptographic failure is a security weakness that occurs when cryptography is missing, implemented incorrectly, or used improperl",

        # app startup wait
        "startup_delay": 10,

        "steps": [
            {
                "text": "Lets see real time demonstration.",
                "action": None,
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/phpmyadmin/index.php?route=/sql&db=bank_demo&table=users&pos=0"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },

            
            {
                "text": "Here we can see that we can see that no cryptography is used to store the password in the database.",
                "action": None,
                "delay": 1
            },

            
            {
                "text": "",
                "action": ("hotkey", ["ctrl", "q"]),
                "delay": 1
            }
        ]
    },

    "ssrf": {

        "definition":
            "Sql Injection refers to user input is executed as SQL commands on a database.",

        # app startup wait
        "startup_delay": 10,

        "steps": [
            {
                "text": "Lets see how the sql injection makes a website vulnerable",
                "action": None,
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/login.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },

            #browser opened now..

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },

            {
                "text": "",
                "action": ("write", "admin' OR '1'='1"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },

            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 7
            },
            #==============================

            {
                "text": "Now lets see how ssrf works",
                "action": None,
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },{
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/system_status.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 5
            },
            {
                "text": "As you can see that server side request forgery can happen",
                "action": None,
                "delay": 5
            },
            

            {
                "text": "",
                "action": ("hotkey", ['ctrl','d']),
                "delay": 5
            },


        ]
    },
    "ssrf": {
    
        "definition":
            "Identificaton and authentication failures occur when an application does not properly verify the identity of users or systems, leading to unauthorized access and potential security breaches.",
    
        # app startup wait
        "startup_delay": 10,
    
        "steps": [
            {
                "text": "Lets see how the identification and authentication failures makes a website vulnerable",
                "action": None,
                "delay": 1
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "tab"),
                "delay": 0.5
            },
            {
                "text": "",
                "action": (
                    "write",
                    "http://127.0.0.1/vuln_bank_demo/admin.php"
                ),
                "delay": 0.5
            },
            {
                "text": "",
                "action": ("press", "enter"),
                "delay": 1
            }, 
            {
                "text": "As we can see that we can access the admin page without any authentication.",
                "action": None,
                "delay": 1
            },   
            {
                "text": "",
                "action": ("hotkey", ["ctrl","q"]),
                "delay": 0.5
            },                      
    
    
        ]
    },

}



# =========================
# MACHINE LEARNING MODEL
# =========================

training_data = [

    ("what is xss", "xss"),
    ("xss", "xss"),

    ("sql injection", "sql injection"),
    ("what is sql injection", "sql injection"),

    ("broken access control", "sql injection"),
    ("what is broken access control", "sql injection"),

    ("csrf", "csrf"),
    ("what is csrf", "csrf"),

    ("cryptographic failure", "cryptographic failure"),
    ("what is cryptographic failure", "cryptographic failure"),

    ("ssrf", "ssrf"),
    ("what is ssrf", "ssrf"),
    
    ("identification and authentication failures", "identification and authentication failures"),
    ("what is identification and authentication failures", "identification and authentication failures"),
]

texts = [t for t, _ in training_data]
labels = [l for _, l in training_data]

model = make_pipeline(
    TfidfVectorizer(),
    LogisticRegression()
)

model.fit(texts, labels)


# =========================
# FUZZY MATCH
# =========================

def fuzzy_match(text):

    match = difflib.get_close_matches(
        text,
        OWASP.keys(),
        n=1,
        cutoff=0.5
    )

    return match[0] if match else None


# =========================
# SHOW VULNERABILITY
# =========================

def show_vulnerability(topic):

    data = OWASP[topic]

    print("\n==============================")
    print("📌 TOPIC:", topic.upper())
    print("==============================")

    print("\n🧠 Definition:")
    print(data["definition"])

    speak(data["definition"])

    print("\n🔧 Prevention Steps:")

    # launch appimage without blocking
    subprocess.Popen(
        ['./neo-1.0.0.AppImage'],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
        start_new_session=True
    )

    # configurable startup wait
    time.sleep(data.get("startup_delay", 3))

    # execute automation steps
    for step in data["steps"]:

        print("-", step["text"])

        execute_action(step["action"])

        if step["text"] != "":
            speak(step["text"])

        # per-step configurable delay
        time.sleep(step.get("delay", 1))


# =========================
# CHAT LOOP
# =========================

print(r'''
        ````````````````````````````````````````
        ``````````````````````````┌─┐``M````````
        ``````````````````````````└┼┘``e````````
        ```````````````````````````│```t````````
        ```````````````````````````│```a````````
        ``````┌───────────────────┐│```m````````
        ``````│                   ││```o````````
        ``````│   ┌───────────┐   ││```r````````
        ``````│   │ ──    ──  │   ┼┘```p````````
        ``````│   └───────────┘   │````h````````
        ``````│                   │````o````````
        ``````│                   │````s````````
        ``````│                   │````i````````
        ``````│    ───────────    │````s````````
        ``````│      ───────      │`````````````
        ``````│                   │````A````````
        ``````└───────────────────┘````I````````
        ````````````````````````````````````````
        `````````Metamorphosis`AI```````````````
        ````````````````````````````````````````

''')


while True:

    q = input("\nYou: ").lower().strip()

    if q == "exit":
        print("👋 Goodbye!")
        break

    # greetings
    greet = detect_greeting(q)

    if greet:
        handle_greeting(greet)
        continue

    topic = None

    # direct match
    for key in OWASP:

        if key in q:
            topic = key
            break

    # fuzzy match
    if not topic:
        topic = fuzzy_match(q)

    # ML fallback
    if not topic:

        intent = model.predict([q])[0]

        if intent in OWASP:
            topic = intent

    # run topic
    if topic:

        print("[INFO] Running...")
        time.sleep(1)

        show_vulnerability(topic)

    else:
        print(
            "❌ I only understand OWASP topics or greetings."
        )


