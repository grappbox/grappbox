package com.grappbox.grappbox.grappbox.Timeline;

import android.app.Activity;
import android.content.Context;
import android.content.res.Resources;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.SimpleAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Objects;

/**
 * Created by tan_f on 30/05/2016.
 */

public class MessageAdapter extends BaseAdapter implements View.OnClickListener {

    private Activity _ac;
    private TimelineListFragment _context;
    private ArrayList _data;
    private static LayoutInflater _inflater = null;
    public Resources _res;
    private MessageModel tmpValue = null;
    int i = 0;

    public static class ViewHolder{
        public TextView timeline_message_title;
        public TextView timeline_message_description;
        public TextView timeline_edit_date;
        public TextView timeline_edit_hour;
        public TextView timeline_message_user;
        public Button   timeline_message_edit;
        public Button   timeline_message_delete;
        public Button   timeline_message_comment;
    }

    public MessageAdapter(Activity activity, ArrayList arrayList, Resources resLocal, TimelineListFragment context){

        _ac = activity;
        _data = arrayList;
        _res = resLocal;

        _inflater = (LayoutInflater)activity.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        _context = context;
    }


    @Override
    public View getView(int position, View convertView, ViewGroup parent){
        View v = convertView;
        ViewHolder holder;
        Button editMessage;
        Button deleteMessage;
        Button commentMessage;

        if (convertView == null){
            v = _inflater.inflate(R.layout.item_timeline_message, null);
            holder = new ViewHolder();
            holder.timeline_edit_date = (TextView)v.findViewById(R.id.timeline_edit_date);
            holder.timeline_edit_hour = (TextView)v.findViewById(R.id.timeline_edit_hour);
            holder.timeline_message_title = (TextView)v.findViewById(R.id.timelie_message_title);
            holder.timeline_message_description = (TextView)v.findViewById(R.id.timelie_message_description);
            holder.timeline_message_user = (TextView)v.findViewById(R.id.timeline_message_user);
            editMessage = (Button) v.findViewById(R.id.timeline_button_edit);
            commentMessage = (Button) v.findViewById(R.id.timeline_button_comment);
            deleteMessage = (Button) v.findViewById(R.id.timeline_button_delete);
            holder.timeline_message_comment = commentMessage;
            holder.timeline_message_delete = deleteMessage;
            holder.timeline_message_edit = editMessage;
            v.setTag(holder);
        } else {
          holder = (ViewHolder)v.getTag();
        }

        if (_data.size() <= 0){
            holder.timeline_message_title.setText("Nothing");
        } else {
            tmpValue = null;
            tmpValue = (MessageModel)_data.get(position);
            holder.timeline_edit_date.setText(tmpValue.getDate());
            holder.timeline_edit_hour.setText(tmpValue.getHour());
            holder.timeline_message_title.setText(tmpValue.getTitle());
            holder.timeline_message_description.setText(tmpValue.getDesc());
            holder.timeline_message_user.setText(tmpValue.getUser());
            editMessage = (Button) v.findViewById(R.id.timeline_button_edit);
            commentMessage = (Button) v.findViewById(R.id.timeline_button_comment);
            deleteMessage = (Button) v.findViewById(R.id.timeline_button_delete);
            holder.timeline_message_comment = commentMessage;
            holder.timeline_message_delete = deleteMessage;
            holder.timeline_message_edit = editMessage;
            editMessage.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _context.editTimelineMessage(position);
                    Log.v("OnClick", "EditMessage");
                }
            });
            commentMessage.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _context.showCommentMessage(position);
                    Log.v("OnClick", "CommentMessage");
                }
            });
            deleteMessage.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _context.archiveTimelineMessage(position);
                    Log.v("OnClick", "DeleteMessage");
                }
            });
        }

        return v;
    }

    @Override
    public int getCount()
    {
        if (_data.size() <= 0)
            return 1;
        return _data.size();
    }

    @Override
    public Object getItem(int position){
        return position;
    }

    @Override
    public long getItemId(int position){
        return position;
    }

    @Override
    public void onClick(View v) {
        Log.v("CustomAdapter", "=====Row button clicked=====");
    }

    /********* Called when Item click in ListView ************/
    private class OnItemClickListener  implements View.OnClickListener {
        private int mPosition;

        OnItemClickListener(int position){
            mPosition = position;
        }

        @Override
        public void onClick(View arg0) {

        }
    }
}
