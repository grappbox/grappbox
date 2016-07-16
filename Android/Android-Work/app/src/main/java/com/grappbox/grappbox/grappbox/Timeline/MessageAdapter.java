package com.grappbox.grappbox.grappbox.Timeline;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.res.Resources;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageButton;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

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

    public static class ViewHolder{
        public TextView timeline_message_title;
        public TextView timeline_message_description;
        public TextView timeline_edit_date;
        public TextView timeline_edit_hour;
        public TextView timeline_message_user;
        public ImageButton   timeline_message_edit;
        public ImageButton   timeline_message_delete;
        public ImageButton   timeline_message_comment;
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
        ImageButton editMessage;
        ImageButton deleteMessage;
        ImageButton commentMessage;

        if (convertView == null){
            v = _inflater.inflate(R.layout.item_timeline_message, null);
            holder = new ViewHolder();
            holder.timeline_edit_date = (TextView)v.findViewById(R.id.timeline_edit_date);
            holder.timeline_edit_hour = (TextView)v.findViewById(R.id.timeline_edit_hour);
            holder.timeline_message_title = (TextView)v.findViewById(R.id.timelie_message_title);
            holder.timeline_message_description = (TextView)v.findViewById(R.id.timelie_message_description);
            holder.timeline_message_user = (TextView)v.findViewById(R.id.timeline_message_user);
            editMessage = (ImageButton) v.findViewById(R.id.timeline_button_edit);
            commentMessage = (ImageButton) v.findViewById(R.id.timeline_button_comment);
            deleteMessage = (ImageButton) v.findViewById(R.id.timeline_button_delete);
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
            editMessage = (ImageButton) v.findViewById(R.id.timeline_button_edit);
            commentMessage = (ImageButton) v.findViewById(R.id.timeline_button_comment);
            deleteMessage = (ImageButton) v.findViewById(R.id.timeline_button_delete);
            holder.timeline_message_comment = commentMessage;
            holder.timeline_message_delete = deleteMessage;
            holder.timeline_message_edit = editMessage;
            editMessage.setOnClickListener((View view) -> {
                AlertDialog.Builder builder = new AlertDialog.Builder(_ac);
                builder.setTitle(R.string.str_edit_message_timeline_option);
                builder.setItems(R.array.edit_message_timeline,new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        switch (which){
                            case 0:
                                _context.editTimelineMessage(position);
                                break;

                            case 1:
                                _context.convertToTicketBugtracker(position);
                                break;

                            default:
                                break;
                        }
                    }
                });
                builder.show();
            });
            commentMessage.setOnClickListener((View view) -> {
                _context.showCommentMessage(position);
            });
            deleteMessage.setOnClickListener((View view) -> {
                _context.archiveTimelineMessage(position);
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

}
