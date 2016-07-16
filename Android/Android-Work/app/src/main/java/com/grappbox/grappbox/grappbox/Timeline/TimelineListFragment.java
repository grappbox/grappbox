package com.grappbox.grappbox.grappbox.Timeline;

import android.app.AlertDialog;
import android.app.Dialog;
import android.app.ProgressDialog;
import android.content.ContentValues;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import com.grappbox.grappbox.grappbox.BugTracker.BugCreationActivity;
import com.grappbox.grappbox.grappbox.Calendar.AgendaFragment;
import com.grappbox.grappbox.grappbox.R;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 17/02/2016.
 */
public class TimelineListFragment extends TimelineMessage {

    private int _idTimeline = -1;
    private View _rootView = null;
    private TimelineFragment _context;
    private List<ContentValues> _value = null;
    private TimelineListFragment _currentContext = this;
    private Vector<Integer> _idValue = new Vector<Integer>();
    private FloatingActionButton _fab;
    private SwipeRefreshLayout _swiper;
    public SwipeRefreshLayout.OnRefreshListener _refresher;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_list_timeline, container, false);

        _swiper = (SwipeRefreshLayout) _rootView.findViewById(R.id.pull_refresher);
        if (_swiper != null)
        startLoading(_rootView, R.id.loader, _swiper);

        _fab = (FloatingActionButton) _rootView.findViewById(R.id.add_timeline_message);
        _fab.setOnClickListener((View v) -> {
            addMessage();
        });
        _fab.hide();
        _refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {

                _swiper.setRefreshing(false);
            }
        };
        _swiper.setOnRefreshListener(_refresher);

        return _rootView;
    }

    public void setContext(TimelineFragment timeline)
    {
        _context = timeline;
    }

    private void addMessage()
    {
        if (_idTimeline != -1) {
            TimelineListFragment timelineListFragment = this;
            AlertDialog.Builder builder = new AlertDialog.Builder(_context.getActivity());
            builder.setTitle(R.string.str_add_message_timeline_option);
            View dialogView;
            LayoutInflater inflater = getActivity().getLayoutInflater();
            dialogView = inflater.inflate(R.layout.dialog_timeline_send_message, null);
            builder.setView(dialogView);
            builder.setPositiveButton(R.string.str_send_message_timeline, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    final EditText messageTitle = (EditText) dialogView.findViewById(R.id.timeline_message_title);
                    final EditText messageContent = (EditText) dialogView.findViewById(R.id.timelie_message_content);
                    APIRequestTimelineAddMessage addMessage = new APIRequestTimelineAddMessage(timelineListFragment, _idTimeline);
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

    public void getTimeline(int idTimeline)
    {
        _idTimeline = idTimeline;
        APIRequestGetListMessageTimeline getMessage = new APIRequestGetListMessageTimeline(this, idTimeline);
        getMessage.execute();
    }

    public void fillView(List<ContentValues> listMessage)
    {
        ListView message = (ListView) _rootView.findViewById(R.id.list_timeline_message);
        ArrayList<HashMap<String, String>> listTimelineMessage = new ArrayList<HashMap<String, String>>();

        SimpleDateFormat dateformat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        SimpleDateFormat dayFormat = new SimpleDateFormat("yyyy-MM-dd");

        Collections.reverse(listMessage);
        _value = listMessage;
        ArrayList<MessageModel> messageModels = new ArrayList<MessageModel>();
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

        MessageAdapter adapter = new MessageAdapter(this.getActivity(), messageModels, getResources(), this);
        message.setAdapter(adapter);
        endLoading();
        _fab.show();
    }

    public void showCommentMessage(int position)
    {
        int idMessage = _idValue.get(position);
        String title = "unknow";
        String content = "unknow";

        for (ContentValues item : _value){
            if (idMessage == Integer.parseInt(item.get("id").toString())){
                title = item.get("title").toString();
                content = item.get("message").toString();
            }
        }
        _context.TimelineShowCommentMessage(idMessage, _idTimeline, title, content);
    }

    public void convertToTicketBugtracker(int position)
    {
        int idMessage = _idValue.get(position);
        String title = "";
        String content = "";

        for (ContentValues item : _value){
            if (idMessage == Integer.parseInt(item.get("id").toString())){
                title = item.get("title").toString();
                content = item.get("message").toString();
            }
        }
        _context.TimelineConvertToTicketBugtracker(title, content);
    }

    public void archiveTimelineMessage(int position)
    {
        int idMessage = _idValue.get(position);
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        builder.setTitle("Timeline message").setMessage("Are you sure you want to archive this message ?");
        builder.setPositiveButton("Archive", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                APIRequestTimelineArchiveMessage archive = new APIRequestTimelineArchiveMessage(_currentContext, _idTimeline, idMessage);
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

    public void editTimelineMessage(int position)
    {
        int idMessage = _idValue.get(position);
        TimelineListFragment timelineListFragment = this;
        AlertDialog.Builder builder = new AlertDialog.Builder(_context.getActivity());
        builder.setTitle(R.string.str_add_message_timeline_option);
        View dialogView;
        LayoutInflater inflater = getActivity().getLayoutInflater();
        dialogView = inflater.inflate(R.layout.dialog_timeline_send_message, null);
        final EditText messageTitle = (EditText) dialogView.findViewById(R.id.timeline_message_title);
        final EditText messageContent = (EditText) dialogView.findViewById(R.id.timelie_message_content);
        for (ContentValues value : _value)
        {
            if (Integer.parseInt(value.getAsString("id")) == idMessage)
            {
                messageTitle.setText(value.getAsString("title"));
                messageContent.setText(value.getAsString("message"));
            }
        }
        builder.setView(dialogView);
        builder.setPositiveButton(R.string.str_send_message_timeline, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                APIRequestTimelineEditMessage editMessage = new APIRequestTimelineEditMessage(timelineListFragment, _idTimeline);
                editMessage.execute(String.valueOf(idMessage), messageTitle.getText().toString(), messageContent.getText().toString());
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
