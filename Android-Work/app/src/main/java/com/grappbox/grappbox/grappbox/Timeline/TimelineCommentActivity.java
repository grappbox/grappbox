package com.grappbox.grappbox.grappbox.Timeline;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.Dialog;
import android.app.ProgressDialog;
import android.content.ContentValues;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collection;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 05/06/2016.
 */
public class TimelineCommentActivity extends AppCompatActivity {

    private TimelineCommentActivity _activity = this;
    private SwipeRefreshLayout _swipeContainer;
    private TextView _messageTitle;
    private TextView _messageContent;
    private int _idTimeline;
    private int _idMessage;
    private List<ContentValues> _value = null;
    private Vector<Integer> _idValue = new Vector<Integer>();
    private FloatingActionButton _fab;
    private ProgressDialog _progress;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.fragment_list_comment_timeline);
        _fab = (FloatingActionButton) findViewById(R.id.add_timeline_message_comment);
        _fab.setOnClickListener((View v) -> {
            addComment();
        });
        _fab.hide();
        _messageTitle = (TextView) findViewById(R.id.timeline_message_title_comment);
        _messageContent = (TextView) findViewById(R.id.timeline_message_content_comment);
        _swipeContainer = (SwipeRefreshLayout) findViewById(R.id.swipeContainer);
        Bundle extra = getIntent().getExtras();
        if (extra != null){
            _messageTitle.setText(extra.getString("titleMessage"));
            _messageContent.setText(extra.getString("contentMessage"));
            _idMessage = extra.getInt("idMessage");
            _idTimeline = extra.getInt("idTimeline");
        }
        _swipeContainer.setOnRefreshListener(new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                refreshTimeline();
            }
        });
        _progress = new ProgressDialog(this);
        _progress.setMessage(getString(R.string.login_progress_label));
        _progress.setProgressStyle(ProgressDialog.STYLE_SPINNER);
        _progress.setIndeterminate(true);
        _progress.show();
        APIRequestGetCommentMessage api = new APIRequestGetCommentMessage(this, _idTimeline, _idMessage);
        api.execute();
    }

    private void refreshTimeline()
    {
        _swipeContainer.setRefreshing(false);
    }

    private void addComment()
    {
        if (_idTimeline != -1) {
            final Dialog TimelineAddMessage = new Dialog(this);
            TimelineAddMessage.setTitle("Send Message : ");
            TimelineAddMessage.setContentView(R.layout.dialog_timeline_send_message);
            final EditText messageTitle = (EditText) TimelineAddMessage.findViewById(R.id.timeline_message_title);
            final EditText messageContent = (EditText) TimelineAddMessage.findViewById(R.id.timelie_message_content);
            Button confirmChangePass = (Button) TimelineAddMessage.findViewById(R.id.timeline_send_message);
            confirmChangePass.setOnClickListener((View v) -> {

                APIRequestAddMessageComment addMessage = new APIRequestAddMessageComment(this, _idTimeline, _idMessage, TimelineAddMessage);
                addMessage.execute(messageTitle.getText().toString(), messageContent.getText().toString());

            });
            Button cancelChangePass = (Button) TimelineAddMessage.findViewById(R.id.timeline_message_cancel);
            cancelChangePass.setOnClickListener((View v) -> {
                messageTitle.setText("");
                messageContent.setText("");
                TimelineAddMessage.dismiss();
            });
            TimelineAddMessage.show();
        }
    }

    public void fillView(List<ContentValues> listComment)
    {
        ListView message = (ListView) findViewById(R.id.list_timeline_message_comment);
        ArrayList<HashMap<String, String>> listTimelineMessage = new ArrayList<HashMap<String, String>>();

        SimpleDateFormat dateformat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        SimpleDateFormat dayFormat = new SimpleDateFormat("yyyy-MM-dd");

        _value = listComment;
        ArrayList<MessageModel> messageModels = new ArrayList<MessageModel>();

        for (ContentValues item : _value) {
            final MessageModel model = new MessageModel();
            Calendar dateMessage = Calendar.getInstance();

            _idValue.add(Integer.parseInt(item.get("id").toString()));
            try {
                dateMessage.setTime(dateformat.parse(item.get("Date").toString()));
                model.setDate(dayFormat.format(dateMessage.getTime()));
                model.setHour(hourFormat.format(dateMessage.getTime()));
            } catch (ParseException p) {
                Log.e("Date parse", "Parsing error");
            }
            model.setTitle(item.get("title").toString());
            model.setDesc(item.get("message").toString());
            model.setUser(item.get("creator").toString());
            messageModels.add(model);
        }


        CommentAdapter adapter = new CommentAdapter(this, messageModels, getResources(), this);
        message.setAdapter(adapter);
        message.setSelection(adapter.getCount() - 1);
        _progress.hide();
        _fab.hide();
    }

    public void editTimelineComment(int idComment)
    {
        final Dialog TimelineEditMessage = new Dialog(_activity);
        TimelineEditMessage.setTitle("Send Message : ");
        TimelineEditMessage.setContentView(R.layout.dialog_timeline_send_message);
        final EditText messageTitle = (EditText) TimelineEditMessage.findViewById(R.id.timeline_message_title);
        final EditText messageContent = (EditText) TimelineEditMessage.findViewById(R.id.timelie_message_content);
        Button confirmEditMessage = (Button) TimelineEditMessage.findViewById(R.id.timeline_send_message);
        for (ContentValues item : _value){
            if (idComment == Integer.parseInt(item.get("id").toString())){
                messageTitle.setText(item.get("title").toString());
                messageContent.setText(item.get("message").toString());
            }
        }
        confirmEditMessage.setOnClickListener((View v) -> {
            Log.v("IDMessage", String.valueOf(_idMessage));
            APIRequestTimelineEditMessage addMessage = new APIRequestTimelineEditMessage(this, _idTimeline, _idMessage, TimelineEditMessage);
            addMessage.execute(String.valueOf(idComment), messageTitle.getText().toString(), messageContent.getText().toString());

        });
        Button cancelEditMessage = (Button) TimelineEditMessage.findViewById(R.id.timeline_message_cancel);
        cancelEditMessage.setOnClickListener((View v) -> {
            messageTitle.setText("");
            messageContent.setText("");
            TimelineEditMessage.dismiss();
        });
        TimelineEditMessage.show();
    }

    public void archiveTimelineComment(int position)
    {
        int idComment = _idValue.get(position);
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Timeline message").setMessage("Are you sure you want to archive this comment ?");
        builder.setPositiveButton("Archive", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                APIRequestTimelineArchiveMessage archive = new APIRequestTimelineArchiveMessage(_activity , _idTimeline, _idMessage, idComment);
                archive.execute();
            }
        });
        builder.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
            }
        });
        builder.show();
    }
}
