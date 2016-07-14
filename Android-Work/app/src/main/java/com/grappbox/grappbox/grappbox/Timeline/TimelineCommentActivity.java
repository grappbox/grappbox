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
import android.view.LayoutInflater;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Model.LoadingActivity;
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
public class TimelineCommentActivity extends LoadingActivity {

    private TimelineCommentActivity _activity = this;
    private TextView _messageTitle;
    private TextView _messageContent;
    private int _idTimeline;
    private int _idMessage;
    private List<ContentValues> _value = null;
    private Vector<Integer> _idValue = new Vector<Integer>();
    private FloatingActionButton _fab;
    private SwipeRefreshLayout _swiper;
    public SwipeRefreshLayout.OnRefreshListener _refresher;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.fragment_list_comment_timeline);
        _swiper = (SwipeRefreshLayout) findViewById(R.id.pull_refresher);
        _fab = (FloatingActionButton) findViewById(R.id.add_timeline_message_comment);
        if (_fab != null) {
            _fab.setOnClickListener((View v) -> {
                addComment();
            });
            _fab.hide();
        }
        startLoading(R.id.loader, _swiper);
        _messageTitle = (TextView) findViewById(R.id.timeline_message_title_comment);
        _messageContent = (TextView) findViewById(R.id.timeline_message_content_comment);
        _refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                _swiper.setRefreshing(false);
            }
        };
        _swiper.setOnRefreshListener(_refresher);

        Bundle extra = getIntent().getExtras();
        if (extra != null){
            _messageTitle.setText(extra.getString("titleMessage"));
            _messageContent.setText(extra.getString("contentMessage"));
            _idMessage = extra.getInt("idMessage");
            _idTimeline = extra.getInt("idTimeline");
        }
        APIRequestGetCommentMessage api = new APIRequestGetCommentMessage(this, _idTimeline, _idMessage);
        api.execute();
    }

    private void addComment()
    {
        if (_idTimeline != -1) {
            TimelineCommentActivity activity = this;
            AlertDialog.Builder builder = new AlertDialog.Builder(activity);
            builder.setTitle(R.string.str_add_message_timeline_option);
            View dialogView;
            LayoutInflater inflater = activity.getLayoutInflater();
            dialogView = inflater.inflate(R.layout.dialog_timeline_send_message, null);
            builder.setView(dialogView);
            builder.setPositiveButton(R.string.str_send_message_timeline, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    final EditText messageTitle = (EditText) dialogView.findViewById(R.id.timeline_message_title);
                    final EditText messageContent = (EditText) dialogView.findViewById(R.id.timelie_message_content);
                    APIRequestTimelineAddMessage addMessage = new APIRequestTimelineAddMessage(activity, _idTimeline, _idMessage);
                    addMessage.execute(messageTitle.getText().toString(), messageContent.getText().toString());
                }
            });
            builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {

                }
            });
            builder.show();
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
        _idValue.clear();

        for (ContentValues item : _value) {
            final MessageModel model = new MessageModel();
            Calendar dateMessage = Calendar.getInstance();

            _idValue.add(0, Integer.parseInt(item.get("id").toString()));
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
            messageModels.add(0, model);
        }

        CommentAdapter adapter = new CommentAdapter(this, messageModels, getResources(), this);
        message.setAdapter(adapter);
        adapter.notifyDataSetChanged();
        endLoading();
        _fab.show();
    }

    public void editTimelineComment(int position)
    {
        int comment = _idValue.get(position);
        TimelineCommentActivity activity = this;
        AlertDialog.Builder builder = new AlertDialog.Builder(activity);
        builder.setTitle(R.string.str_add_message_timeline_option);
        View dialogView;
        LayoutInflater inflater = activity.getLayoutInflater();
        dialogView = inflater.inflate(R.layout.dialog_timeline_send_message, null);
        final EditText messageTitle = (EditText) dialogView.findViewById(R.id.timeline_message_title);
        final EditText messageContent = (EditText) dialogView.findViewById(R.id.timelie_message_content);
        Log.v("Value", _value.toString());
        Log.v("idComment", String.valueOf(comment));
        for (ContentValues value : _value)
        {
            if (Integer.parseInt(value.getAsString("id")) == comment)
            {
                messageTitle.setText(value.getAsString("title"));
                messageContent.setText(value.getAsString("message"));
            }
        }
        builder.setView(dialogView);
        builder.setPositiveButton(R.string.str_send_message_timeline, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                APIRequestTimelineEditMessage addMessage = new APIRequestTimelineEditMessage(activity, _idTimeline, _idMessage);
                addMessage.execute(String.valueOf(comment), messageTitle.getText().toString(), messageContent.getText().toString());
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {

            }
        });
        builder.show();
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
