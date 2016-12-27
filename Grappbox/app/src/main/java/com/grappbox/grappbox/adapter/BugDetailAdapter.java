package com.grappbox.grappbox.adapter;

import android.app.Activity;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Handler;
import android.support.design.widget.TextInputEditText;
import android.support.v4.util.Pair;
import android.support.v4.view.AsyncLayoutInflater;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.BugCommentModel;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.model.BugTagModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.receiver.ErrorReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.BugtrackerJIT;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.ArrayList;
import java.util.Collection;
import java.util.HashSet;
import java.util.List;

/**
 * Created by marc on 07/10/2016.
 */

public class BugDetailAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private static final int TYPE_TAGS = 0;
    private static final int TYPE_ASSIGNEES = 1;
    private static final int TYPE_SUBTITLE = 2;
    private static final int TYPE_COMMENT = 3;
    private static final int TYPE_COMMENT_REPLY = 4;

    private List<BugTagModel> mTags;
    private List<UserModel> mAssignees;
    private List<BugCommentModel> mComments;
    private BugModel mBug;

    private LayoutInflater mInflater;
    private Context mContext;
    private View.OnClickListener mReplyListener;

    public BugDetailAdapter(Context context) {
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mTags = new ArrayList<>();
        mAssignees = new ArrayList<>();
        mComments = new ArrayList<>();
    }

    public List<BugCommentModel> getComments(){
        return mComments;
    }

    public List<BugTagModel> getTags(){
        return mTags;
    }

    public List<UserModel> getAssignees(){
        return mAssignees;
    }

    public void setBugModel(BugModel model){
        mBug = model;
        mTags = model.tags;
        mAssignees = model.assignees;
        notifyDataSetChanged();
    }

    public void setComments(List<BugCommentModel> comms){
        mComments = comms;
        notifyDataSetChanged();
    }

    private RecyclerView.ViewHolder createTagsViewHolder(ViewGroup parent){
        RecyclerView.ViewHolder vh = new HorizontalHolder(mInflater.inflate(R.layout.list_item_bug_tags, parent, false));
        return vh;
    }

    private RecyclerView.ViewHolder createAssigneesViewHolder(ViewGroup parent){
        RecyclerView.ViewHolder vh = new HorizontalHolder(mInflater.inflate(R.layout.list_item_bug_assignees, parent, false));
        return vh;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_TAGS:
                return createTagsViewHolder(parent);
            case TYPE_ASSIGNEES:
                return createAssigneesViewHolder(parent);
            case TYPE_SUBTITLE:
                return new SubtitleHolder(mInflater.inflate(R.layout.list_item_custom_title, parent, false));
            case TYPE_COMMENT:
                return new CommentHolder(mInflater.inflate(R.layout.list_item_bug_comment, parent, false));
            case TYPE_COMMENT_REPLY:
                return new CommentReplyHolder(mInflater.inflate(R.layout.list_item_bug_comment_reply, parent, false));
            default:
                throw new IllegalArgumentException("Bad viewType : " + viewType);
        }
    }

    @Override
    public int getItemViewType(int position) {
        switch (position){
            case TYPE_TAGS:
            case TYPE_ASSIGNEES:
            case TYPE_SUBTITLE:
                return position;
            default:
                if (position == getItemCount() - 1)
                    return TYPE_COMMENT_REPLY;
                return TYPE_COMMENT;
        }
    }

    private void bindTags(HorizontalHolder holder){
        if (mTags.isEmpty()){
            holder.emptycontainer.setVisibility(View.VISIBLE);
        } else {
            new LazyTagLoading(mContext, holder, holder.container).execute(mTags.toArray(new BugTagModel[mTags.size()]));
        }
    }

    private void bindAssignees(HorizontalHolder holder){
        if (mAssignees.isEmpty()){
            holder.emptycontainer.setVisibility(View.VISIBLE);
        } else {
            new LazyAssigneeLoading(mContext, holder, holder.container).execute(mAssignees.toArray(new UserModel[mAssignees.size()]));
        }
    }

    public void setReplyClickListener(View.OnClickListener clickListener){
        mReplyListener = clickListener;
    }

    private void bindComment(CommentHolder holder, final BugCommentModel data){
        holder.comment.setText(data.mDescription);
        holder.date.setText(data.mDate);
        holder.username.setText(data.mAuthor.mFirstname + " " + data.mAuthor.mLastname);
        holder.itemView.setOnClickListener(new onListItemClicked(holder));
        if (data.mAuthor.mEmail.equals(Session.getInstance(mContext).getCurrentAccount().name)){
            holder.edit.setVisibility(View.VISIBLE);
            holder.delete.setVisibility(View.VISIBLE);
            holder.edit.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    AlertDialog.Builder builder = new AlertDialog.Builder(mContext);

                    builder.setTitle(R.string.edit_title_dialog_title);
                    builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            dialog.cancel();
                        }
                    });
                    builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface idialog, int which) {
                            AlertDialog dialog = (AlertDialog) idialog;

                            TextView comView = ((TextView)dialog.findViewById(R.id.comment));
                            assert comView != null;
                            String comment = comView.getText().toString();
                            Intent edit = new Intent(mContext, BugtrackerJIT.class);
                            edit.setAction(BugtrackerJIT.ACTION_EDIT_COMMENT);
                            edit.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, new ErrorReceiver(new Handler(), (Activity) mContext));
                            edit.putExtra(BugtrackerJIT.EXTRA_BUG_ID, mBug._id);
                            edit.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, mBug.projectID);
                            edit.putExtra(GrappboxJustInTimeService.EXTRA_COMMENT_ID, data._id);
                            edit.putExtra(GrappboxJustInTimeService.EXTRA_MESSAGE, comment);
                            mContext.startService(edit);
                        }
                    });
                    builder.setView(R.layout.dialog_edit_bug_comment);
                    ((TextView)builder.show().findViewById(R.id.comment)).setText(data.mDescription);
                }
            });

            holder.delete.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    AlertDialog.Builder confirm = new AlertDialog.Builder(mContext);
                    confirm.setTitle(R.string.confirm_erase_comment_title);
                    confirm.setMessage(R.string.erase_confirm_comment_long);
                    confirm.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            Intent delete = new Intent(mContext, GrappboxJustInTimeService.class);
                            delete.setAction(GrappboxJustInTimeService.ACTION_DELETE_COMMENT);
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_COMMENT_ID, data._id);
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, new ErrorReceiver(new Handler(), (Activity) mContext));
                            mContext.startService(delete);
                        }
                    });
                    confirm.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            dialog.cancel();
                        }
                    });
                    confirm.show();
                }
            });
        } else {
            holder.edit.setVisibility(View.GONE);
            holder.delete.setVisibility(View.GONE);
        }
        holder.reply.setOnClickListener(mReplyListener);

    }

    private void bindReply(final CommentReplyHolder holder){
        holder.send.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Log.e("test", String.valueOf(mBug.grappboxId));
                Intent postComment = new Intent(mContext, BugtrackerJIT.class);
                postComment.setAction(BugtrackerJIT.ACTION_POST_COMMENT);
                postComment.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID ,mBug.projectID);
                postComment.putExtra(BugtrackerJIT.EXTRA_BUG_ID ,mBug._id);
                postComment.putExtra(GrappboxJustInTimeService.EXTRA_MESSAGE ,holder.comment.getText().toString());
                postComment.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, new ErrorReceiver(new Handler(), (Activity) mContext));
                mContext.startService(postComment);
                holder.comment.setText("");
            }
        });
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_TAGS:
                bindTags((HorizontalHolder) holder);
                break;
            case TYPE_ASSIGNEES:
                bindAssignees((HorizontalHolder) holder);
                break;
            case TYPE_SUBTITLE:
                break;
            case TYPE_COMMENT:
                bindComment((CommentHolder) holder, mComments.get(position - 3));
                break;
            case TYPE_COMMENT_REPLY:
                bindReply((CommentReplyHolder) holder);
                break;
            default:
                throw new IllegalArgumentException("Bad viewType : " + getItemViewType(position));
        }
    }

    @Override
    public int getItemCount() {
        return 4 + mComments.size();
    }

    private static class HorizontalHolder extends RecyclerView.ViewHolder{
        public LinearLayout container;
        public TextView emptycontainer;
        public HorizontalHolder(View itemView) {
            super(itemView);
            container = (LinearLayout) itemView.findViewById(R.id.container);
            emptycontainer = (TextView) itemView.findViewById(R.id.emptyview);
        }
    }

    private static class CommentHolder extends RecyclerView.ViewHolder{
        public TextView username, comment, date;
        public ImageButton reply, edit, delete;
        public ImageView avatar;
        public LinearLayout actionContainer;

        public CommentHolder(View itemView) {
            super(itemView);
            username = (TextView) itemView.findViewById(R.id.username);
            comment = (TextView) itemView.findViewById(R.id.comment);
            date = (TextView) itemView.findViewById(R.id.date);
            reply = (ImageButton) itemView.findViewById(R.id.reply);
            edit = (ImageButton) itemView.findViewById(R.id.edit);
            delete = (ImageButton) itemView.findViewById(R.id.delete);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
            actionContainer = (LinearLayout) itemView.findViewById(R.id.action_container);
        }
    }

    private static class CommentReplyHolder extends RecyclerView.ViewHolder{
        public ImageView avatar;
        public ImageButton send;
        public TextInputEditText comment;

        CommentReplyHolder(View itemView) {
            super(itemView);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
            send = (ImageButton) itemView.findViewById(R.id.reply);
            comment = (TextInputEditText) itemView.findViewById(R.id.comment);
        }
    }

    private static class SubtitleHolder extends RecyclerView.ViewHolder{
        public TextView title;

        SubtitleHolder(View itemView) {
            super(itemView);
            title = (TextView) itemView.findViewById(R.id.title);
        }
    }

    private static class LazyTagLoading extends AsyncTask<BugTagModel, Void, List<Pair<View, BugTagModel>>>{
        private ViewGroup parent;
        private LayoutInflater inflater;
        private HorizontalHolder holder;

        LazyTagLoading(Context context, HorizontalHolder holder, ViewGroup parent){
            inflater = LayoutInflater.from(context);
            this.parent = parent;
            this.holder = holder;
        }

        @Override
        protected List<Pair<View, BugTagModel>> doInBackground(BugTagModel... params) {
            List<Pair<View, BugTagModel>> modelView = new ArrayList<>();
            for (int i = 0; i < params.length; ++i){
                BugTagModel current = params[i];
                View v = inflater.inflate(R.layout.list_item_bugtracker_tagitem, parent, false);
                modelView.add(new Pair<>(v, current));
            }
            return modelView;
        }

        @Override
        protected void onPostExecute(List<Pair<View, BugTagModel>> pairs) {
            holder.emptycontainer.setVisibility(View.GONE);
            holder.container.removeAllViewsInLayout();
            holder.container.addView(holder.emptycontainer);
            for(Pair<View, BugTagModel> viewModel : pairs){
                ((TextView)viewModel.first.findViewById(R.id.tagname)).setText(viewModel.second.name);
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP){
                    viewModel.first.findViewById(R.id.tagname).setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(viewModel.second.color)));
                } else {
                    viewModel.first.findViewById(R.id.tagname).setBackgroundColor(Color.parseColor(viewModel.second.color));
                }
                holder.container.addView(viewModel.first);
            }
        }
    }

    private static class LazyAssigneeLoading extends AsyncTask<UserModel, Void, List<Pair<View, UserModel>>>{

        private ViewGroup parent;
        private LayoutInflater inflater;
        private HorizontalHolder holder;

        LazyAssigneeLoading(Context context, HorizontalHolder holder, ViewGroup parent){
            inflater = LayoutInflater.from(context);
            this.parent = parent;
            this.holder = holder;
        }

        @Override
        protected List<Pair<View, UserModel>> doInBackground(UserModel... params) {
            List<Pair<View, UserModel>> modelView = new ArrayList<>();
            for (UserModel model : params){
                View v = inflater.inflate(R.layout.bug_assignee, parent, false);
                modelView.add(new Pair<>(v, model));
            }
            return modelView;
        }

        @Override
        protected void onPostExecute(List<Pair<View, UserModel>> pairs) {
            holder.emptycontainer.setVisibility(View.GONE);
            holder.container.removeAllViewsInLayout();
            holder.container.addView(holder.emptycontainer);
            for (Pair<View, UserModel> viewModel : pairs){
                ((TextView)viewModel.first.findViewById(R.id.username)).setText(viewModel.second.mFirstname + " " + viewModel.second.mLastname);
                holder.container.addView(viewModel.first);
            }

        }
    }

    private static class onListItemClicked implements View.OnClickListener{
        private CommentHolder data;
        private static View lastExpanded = null;

        onListItemClicked(CommentHolder holder){
            data = holder;
        }

        @Override
        public void onClick(View v) {
            if (lastExpanded != null && lastExpanded != v && lastExpanded.findViewById(R.id.action_container).getVisibility() == View.VISIBLE)
                lastExpanded.callOnClick();
            v.setOnClickListener(new onExpandedListItemClicked(data));
            data.actionContainer.setVisibility(View.VISIBLE);
            lastExpanded = v;
        }
    }



    private static class onExpandedListItemClicked implements View.OnClickListener{
        private CommentHolder data;

        onExpandedListItemClicked(CommentHolder holder){
            data = holder;
        }

        @Override
        public void onClick(View v) {
            v.setOnClickListener(new onListItemClicked(data));
            data.actionContainer.setVisibility(View.GONE);
        }
    }
}
