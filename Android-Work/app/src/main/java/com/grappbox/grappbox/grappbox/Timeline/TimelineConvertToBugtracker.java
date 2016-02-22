package com.grappbox.grappbox.grappbox.Timeline;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.BugTracker.CreateBugTask;
import com.grappbox.grappbox.grappbox.BugTracker.GetBugTagTask;
import com.grappbox.grappbox.grappbox.BugTracker.GetProjectUserTask;
import com.grappbox.grappbox.grappbox.R;

/**
 * Created by tan_f on 22/02/2016.
 */

public class TimelineConvertToBugtracker extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_bug_creation);

        LinearLayout tagList = (LinearLayout) findViewById(R.id.ll_categories);
        LinearLayout userList = (LinearLayout) findViewById(R.id.ll_assignee);
        Button save = (Button) findViewById(R.id.btn_save);

        Intent intent = getIntent();
        String title = intent.getStringExtra("title");
        String content = intent.getStringExtra("content");
        GetBugTagTask bttask = new GetBugTagTask(this, tagList);
        bttask.execute();
        GetProjectUserTask puttask = new GetProjectUserTask(this, userList);
        puttask.execute();
        Activity me = this;
        LinearLayout rootlayout = (LinearLayout) findViewById(R.id.ll_root);
        CreateBugTask cbtask = new CreateBugTask(me, rootlayout);
        cbtask.fillText(title, content);
        save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                cbtask.execute();
            }
        });
    }
}