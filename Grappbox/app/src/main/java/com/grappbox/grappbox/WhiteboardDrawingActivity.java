package com.grappbox.grappbox;

import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;

import com.grappbox.grappbox.receiver.WhiteboardReceiver;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.List;

public class WhiteboardDrawingActivity extends AppCompatActivity implements WhiteboardReceiver.Callbacks {
    public static final String EXTRA_WHITEBOARD_ID = "com.grappbox.extra.whiteboard_id";
    public static final String EXTRA_WHITEBOARD_NAME = "com.grappbox.extra.whiteboard_name";

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
    protected void onDestroy() {
        Intent close = new Intent(this, GrappboxWhiteboardJIT.class);
        close.setAction(GrappboxWhiteboardJIT.ACTION_CLOSE);
        close.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, getIntent().getStringExtra(EXTRA_WHITEBOARD_ID));
        startService(close);
        super.onDestroy();
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        final WhiteboardDrawingActivity instance = this;

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_whiteboard_drawing);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setBackgroundDrawable(new ColorDrawable(ContextCompat.getColor(this, R.color.GrappGreen)));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
        getSupportActionBar().setTitle(getIntent().getStringExtra(EXTRA_WHITEBOARD_NAME));
        mListeners = new ArrayList<>();
        Intent open = new Intent(this, GrappboxWhiteboardJIT.class);
        open.setAction(GrappboxWhiteboardJIT.ACTION_OPEN);
        open.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, getIntent().getStringExtra(EXTRA_WHITEBOARD_ID));
        open.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, new WhiteboardReceiver(this));
        startService(open);


    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.whiteboard_menu, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()){
            case android.R.id.home:
                onBackPressed();
                return true;
        }
        return false;
    }

    @Override
    public void onReceivedObjects(JSONArray objects) {
        for (ResultDispatcher listener : mListeners) {
            listener.onOpen(objects);
        }
    }
}
