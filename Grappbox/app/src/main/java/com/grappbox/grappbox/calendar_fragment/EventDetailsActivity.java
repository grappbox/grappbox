package com.grappbox.grappbox.calendar_fragment;

import android.app.Activity;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

/**
 * Created by tan_f on 16/11/2016.
 */

public class EventDetailsActivity extends AppCompatActivity {

    private static final String LOG_TAG = EventDetailsActivity.class.getSimpleName();
    public static final String EXTRA_CALENDAR_EVENT_MODEL = "com.grappbox.grappbox.calendar_fragments.EXTRA_CALENDAR_EVENT_MODEL";
    private CalendarEventModel mData;
    private Activity mContext = this;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_calendar_event_details);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setBackgroundDrawable(new ColorDrawable(ContextCompat.getColor(this, R.color.GrappBlue)));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(Utils.Color.getThemeAccentColor(this));
        }
        mData = getIntent().getParcelableExtra(EXTRA_CALENDAR_EVENT_MODEL);
        getSupportActionBar().setTitle(mData._title);
    }

    @Override
    public void setTitle(CharSequence title) {
        super.setTitle(title);
        getSupportActionBar().setTitle(title);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.event_details_option_menu, menu);
        return true;
    }

    private void actionEdit()
    {

    }

    private void actionDelete()
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Delete event");
        builder.setMessage("Are you sure you want to delete this event ?");
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                Intent delete = new Intent(mContext, GrappboxJustInTimeService.class);
                delete.setAction(GrappboxJustInTimeService.ACTION_DELETE_EVENT);
                delete.putExtra(GrappboxJustInTimeService.EXTRA_EVENT_ID, mData._id);
                startService(delete);
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        builder.show();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case android.R.id.home:
                onBackPressed();
                break;

            case R.id.action_edit:
                actionEdit();
                return true;

            case R.id.action_delete:
                actionDelete();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }
}
