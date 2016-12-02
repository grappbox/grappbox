package com.grappbox.grappbox;

import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.app.Activity;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;

import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.receiver.WhiteboardListReceiver;
import com.grappbox.grappbox.receiver.WhiteboardReceiver;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;
import com.grappbox.grappbox.views.Whiteboard;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.List;

public class WhiteboardDrawingActivity extends AppCompatActivity implements WhiteboardReceiver.Callbacks {
    public static final String EXTRA_WHITEBOARD_ID = "com.grappbox.extra.whiteboard_id";

    public interface ResultDispatcher{
        void onOpen(JSONArray result);
    }

    private List<ResultDispatcher> mListeners;

    public void registerObserver(ResultDispatcher callback){
        if (!mListeners.contains(callback))
            mListeners.add(callback);
    }

    public void unregisterObserver(ResultDispatcher callback){
        mListeners.remove(callback);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_whiteboard_drawing);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setBackgroundDrawable(new ColorDrawable(ContextCompat.getColor(this, R.color.GrappGreen)));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(Utils.Color.getThemeAccentColor(this));
        }
        mListeners = new ArrayList<>();
        Intent open = new Intent(this, GrappboxWhiteboardJIT.class);
        open.setAction(GrappboxWhiteboardJIT.ACTION_OPEN);
        open.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, getIntent().getStringExtra(EXTRA_WHITEBOARD_ID));
        open.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, new WhiteboardReceiver(this));
        startService(open);
    }

    @Override
    public void onReceivedObjects(JSONArray objects) {
        for (ResultDispatcher listener : mListeners) {
            listener.onOpen(objects);
        }
    }
}
