package com.grappbox.grappbox.sync;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;

/**
 * Created by marcw on 03/09/2016.
 */
public class GrappboxAuthenticatorService extends Service {
    private GrappboxAuthenticator mAuth;

    @Override
    public void onCreate() {
        mAuth = new GrappboxAuthenticator(this);
    }

    @Override
    public IBinder onBind(Intent intent) {
        return mAuth.getIBinder();
    }
}
