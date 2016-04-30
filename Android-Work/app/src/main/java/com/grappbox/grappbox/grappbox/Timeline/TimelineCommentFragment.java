package com.grappbox.grappbox.grappbox.Timeline;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
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
import java.util.HashMap;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 18/02/2016.
 */
public class TimelineCommentFragment extends TimelineMessage {

    private View _rootView;
    private TextView _messageTitle;
    private TextView _messageContent;
    private List<ContentValues> _value = null;
    private Vector<Integer> _idValue = new Vector<Integer>();
    private int _idTimeline;
    private int _idMessage;
    private TimelineCommentFragment _currentContext;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_list_comment_timeline, container, false);

        FloatingActionButton fab = (FloatingActionButton) _rootView.findViewById(R.id.add_timeline_message_comment);
        fab.setOnClickListener((View v) -> {
            addComment();
        });
        _messageTitle = (TextView) _rootView.findViewById(R.id.timeline_message_title_comment);
        _messageContent = (TextView) _rootView.findViewById(R.id.timeline_message_content_comment);
        _messageTitle.setText(getArguments().getString("titleMessage"));
        _messageContent.setText(getArguments().getString("contentMessage"));
        _idMessage = getArguments().getInt("idMessage");
        _idTimeline = getArguments().getInt("idTimeline");
        _currentContext = this;
        APIRequestGetMessageComment getComment = new APIRequestGetMessageComment(this, _idTimeline, _idMessage);
        getComment.execute();
        return _rootView;
    }

    private void addComment()
    {
        if (_idTimeline != -1) {
            final Dialog TimelineAddMessage = new Dialog(getActivity());
            TimelineAddMessage.setTitle("Send Comment : ");
            TimelineAddMessage.setContentView(R.layout.dialog_timeline_send_message);
            final EditText messageTitle = (EditText) TimelineAddMessage.findViewById(R.id.timeline_message_title);
            final EditText messageContent = (EditText) TimelineAddMessage.findViewById(R.id.timelie_message_content);
            Button confirmChangePass = (Button) TimelineAddMessage.findViewById(R.id.timeline_send_message);
            confirmChangePass.setOnClickListener((View v) -> {

                APIRequestAddMessageComment addComment = new APIRequestAddMessageComment(this, _idTimeline, _idMessage, TimelineAddMessage);
                addComment.execute(messageTitle.getText().toString(), messageContent.getText().toString());

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
        ListView message = (ListView) _rootView.findViewById(R.id.list_timeline_message_comment);
        ArrayList<HashMap<String, String>> listTimelineMessage = new ArrayList<HashMap<String, String>>();

        SimpleDateFormat dateformat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        SimpleDateFormat dayFormat = new SimpleDateFormat("yyyy-MM-dd");

        _value = listComment;
        for (ContentValues item : _value){
            Calendar dateMessage = Calendar.getInstance();
            HashMap<String, String> map = new HashMap<String, String>();

            _idValue.add(Integer.parseInt(item.get("id").toString()));
            try {
                dateMessage.setTime(dateformat.parse(item.get("Date").toString()));
                map.put("timeline_edit_date", dayFormat.format(dateMessage.getTime()));
                map.put("timeline_edit_hour", hourFormat.format(dateMessage.getTime()));

            } catch (ParseException p) {
                Log.e("Date parse", "Parsing error");
            }
            map.put("timeline_message_title", item.get("title").toString());
            map.put("timeline_message_description", item.get("message").toString());
            map.put("timeline_message_user", item.get("creator").toString());
            listTimelineMessage.add(map);
        }

        SimpleAdapter messageAdapter = new SimpleAdapter(_rootView.getContext(), listTimelineMessage, R.layout.item_timeline_message,
                new String[] {"timeline_message_title", "timeline_message_description", "timeline_edit_date", "timeline_edit_hour", "timeline_message_user"},
                new int[] {R.id.timelie_message_title, R.id.timelie_message_description, R.id.timeline_edit_date, R.id.timeline_edit_hour, R.id.timeline_message_user});
        message.setAdapter(messageAdapter);
        message.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                builder.setTitle("Timeline message").setItems(R.array.timeline_message_comment_action, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        switch (which) {

                            case 0:
                                editTimelineComment(_idValue.get(position));
                                break;

                            case 1:
                                archiveTimelineComment(_idValue.get(position));
                                break;

                            default:
                                break;
                        }
                    }
                });
                builder.show();
            }
        });
        message.setSelection(messageAdapter.getCount() - 1);
    }

    private void editTimelineComment(int idComment)
    {
        final Dialog TimelineEditMessage = new Dialog(getActivity());
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

    private void archiveTimelineComment(int idComment)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        builder.setTitle("Timeline message").setMessage("Are you sure you want to archive this comment ?");
        builder.setPositiveButton("Archive", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                APIRequestTimelineArchiveMessage archive = new APIRequestTimelineArchiveMessage(_currentContext, _idTimeline, _idMessage, idComment);
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
