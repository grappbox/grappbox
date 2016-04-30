package com.grappbox.grappbox.grappbox.Whiteboard;

import android.app.AlarmManager;
import android.app.IntentService;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.text.format.DateUtils;
import android.text.format.Time;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;


/**
 * Created by tan_f on 04/04/2016.
 */
public class WhiteboardPullIntentService extends IntentService {

    private final static String _PATH = "whiteboard/pulldraw/";

    public WhiteboardPullIntentService (){
        super("WhiteboardPullIntentService");
    }

    @Override
    protected void onHandleIntent(Intent intent)
    {
        Intent broadcastIntent = new Intent();
        broadcastIntent.setAction(WhiteboardFragment.MyReceiver.ACTION_RESP);
        broadcastIntent.addCategory(Intent.CATEGORY_DEFAULT);
        sendBroadcast(broadcastIntent);
    }

}
