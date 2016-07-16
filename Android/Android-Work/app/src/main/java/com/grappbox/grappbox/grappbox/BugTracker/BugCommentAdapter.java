package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

/**
 * Created by wieser_m on 25/02/2016.
 */
public class BugCommentAdapter extends ArrayAdapter<BugCommentEntity> {
    public BugCommentAdapter(Context context, int resource) {
        super(context, resource);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        View v = convertView;

        if (v == null) {
            LayoutInflater vi;
            vi = LayoutInflater.from(getContext());
            v = vi.inflate(R.layout.li_bug_comment, null);
        }

        BugCommentEntity comment = getItem(position);

        if (comment != null) {
            TextView title = (TextView) v.findViewById(R.id.txt_title);
            TextView authorDate = (TextView) v.findViewById(R.id.txt_author_date);
            TextView content = (TextView) v.findViewById(R.id.txt_content);

            if (title != null)
            {
                title.setText(comment.getTitle());
            }
            if (authorDate != null)
            {
                String adstr = "By " + comment.getAuthorName() + " the " + comment.getDate();
                authorDate.setText(adstr);
            }
            if (content != null)
            {
                content.setText(comment.getContent());
            }
        }

        return v;
    }
}
