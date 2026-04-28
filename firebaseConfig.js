import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';
import { getFirestore } from 'firebase/firestore';

// google-services (1).json dosyasındaki bilgilerinden otomatik oluşturuldu
const firebaseConfig = {
  apiKey: "AIzaSyCH5PKQISIlHks7T6veaIMEhhafdoA4aTc",
  authDomain: "fir-login-36824.firebaseapp.com",
  projectId: "fir-login-36824",
  storageBucket: "fir-login-36824.firebasestorage.app",
  appId: "1:336534749749:android:e0325c3f73231d914c677c"
};

export const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);