package com.grappbox.grappbox.grappbox.Timeline;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ContentValues;
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

import com.grappbox.grappbox.grappbox.Calendar.AgendaFragment;
import com.grappbox.grappbox.grappbox.R;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 17/02/2016.
 */
public class TimelineListFragment extends Fragment {

    private int _idTimeline = -1;
    private View _rootView;
    private TimelineFragment _context;
    private List<ContentValues> _value = null;
    private TimelineListFragment _currentContext = this;
    private Vector<Integer> _idValue = new Vector<Integer>();

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_list_timeline, container, false);

        FloatingActionButton fab = (FloatingActionButton) _rootView.findViewById(R.id.add_timeline_message);
        fab.setOnClickListener((View v) -> {
            addMessage();
        });

        return _rootView;
    }

    public void setContext(TimelineFragment timeline)
    {
        _context = timeline;
    }

    private void addMessage()
    {
        if (_idTimeline != -1) {
            final Dialog TimelineAddMessage = new Dialog(getActivity());
            TimelineAddMessage.setTitle("Send Message : ");
            TimelineAddMessage.setContentView(R.layout.dialog_timeline_send_message);
            final EditText messageTitle = (EditText) TimelineAddMessage.findViewById(R.id.timeline_message_title);
            final EditText messageContent = (EditText) TimelineAddMessage.findViewById(R.id.timelie_message_content);
            Button confirmChangePass = (Button) TimelineAddMessage.findViewById(R.id.timeline_send_message);
            confirmChangePass.setOnClickListener((View v) -> {

                APIRequestTimelineAddMessage addMessage = new APIRequestTimelineAddMessage(this, _idTimeline, TimelineAddMessage);
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

        _value = listMessage;
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
            map.put("timelie_message_title", item.get("title").toString());
            map.put("timelie_message_description", item.get("message").toString());
            listTimelineMessage.add(map);
        }

        SimpleAdapter messageAdapter = new SimpleAdapter(_rootView.getContext(), listTimelineMessage, R.layout.item_timeline_message,
                new String[] {"timelie_message_title", "timelie_message_description", "timeline_edit_date", "timeline_edit_hour"},
                new int[] {R.id.timelie_message_title, R.id.timelie_message_description, R.id.timeline_edit_date, R.id.timeline_edit_hour});
        message.setAdapter(messageAdapter);
        message.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                builder.setTitle("Timeline message").setItems(R.array.timeline_message_action, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        switch (which) {

                            case 0:
                                editTimelineMessage(_idValue.get(position));
                                break;

                            case 1:
                                showCommentMessage(_idValue.get(position));
                                break;

                            case 2:
                                archiveTimelineMessage(_idValue.get(position));
                                break;

                            case 3:
                                convertToTicketBugtracker(_idValue.get(position));
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

    private void showCommentMessage(int idMessage)
    {
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

    private void convertToTicketBugtracker(int idMessage)
    {

    }

    private void archiveTimelineMessage(int idMessage)
    {
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

    private void editTimelineMessage(int idMessage)
    {
        final Dialog TimelineEditMessage = new Dialog(getActivity());
        TimelineEditMessage.setTitle("Send Message : ");
        TimelineEditMessage.setContentView(R.layout.dialog_timeline_send_message);
        final EditText messageTitle = (EditText) TimelineEditMessage.findViewById(R.id.timeline_message_title);
        final EditText messageContent = (EditText) TimelineEditMessage.findViewById(R.id.timelie_message_content);
        Button confirmEditMessage = (Button) TimelineEditMessage.findViewById(R.id.timeline_send_message);
        for (ContentValues item : _value){
            if (idMessage == Integer.parseInt(item.get("id").toString())){
                messageTitle.setText(item.get("title").toString());
                messageContent.setText(item.get("message").toString());
            }
        }
        confirmEditMessage.setOnClickListener((View v) -> {

            APIRequestTimelineEditMessage addMessage = new APIRequestTimelineEditMessage(this, _idTimeline, TimelineEditMessage);
            addMessage.execute(String.valueOf(idMessage), messageTitle.getText().toString(), messageContent.getText().toString());

        });
        Button cancelEditMessage = (Button) TimelineEditMessage.findViewById(R.id.timeline_message_cancel);
        cancelEditMessage.setOnClickListener((View v) -> {
            messageTitle.setText("");
            messageContent.setText("");
            TimelineEditMessage.dismiss();
        });
        TimelineEditMessage.show();
    }

}
