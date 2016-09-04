package com.grappbox.grappbox.sync;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;

public class GrappboxSyncService extends Service {
    private static final Object sSyncAdapterLock = new Object();
    private static GrappboxSyncAdapter sSyncAdapter = null;

    public GrappboxSyncService() {
    }

    @Override
    public void onCreate() {
        synchronized (sSyncAdapterLock) {
            if (sSyncAdapter == null)
                sSyncAdapter = new GrappboxSyncAdapter(getApplicationContext(), true);
        }
    }

    @Override
    public IBinder onBind(Intent intent) {
        return sSyncAdapter.getSyncAdapterBinder();
    }
}
