package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Intent;
import android.graphics.Color;
import android.graphics.Rect;
import android.support.v4.content.ContextCompat;
import android.support.v7.widget.CardView;
import android.support.v7.widget.LinearLayoutCompat;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

/**
 * Created by wieser_m on 30/04/2016.
 */
public class TaskListAdapter extends RecyclerView.Adapter<TaskListAdapter.ViewHolder>
{
    private final long millisecondToDays = 86400000;
    private Task[] dataSet;
    private TaskCardInteraction interaction = null;
    public TaskListAdapter(Task[] dataSet)
    {
        this.dataSet = dataSet;
    }

    public interface TaskCardInteraction
    {
        void onOpenClicked(String ID);
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        CardView v = (CardView) LayoutInflater.from(parent.getContext()).inflate(R.layout.card_tasklist, parent, false);
        //CardView v = (CardView) l.findViewById(R.id.card_view);

        return new ViewHolder(v);
    }

    public void setInteractionObject(TaskCardInteraction newInteraction) { interaction = newInteraction; }

    @Override
    public void onBindViewHolder(ViewHolder holder, int position) {
        Task currentContent = dataSet[position];
        TextView taskTitle, taskProgression, assignedPeople, dayLeft;
        Button openAction;
        LinearLayout layTags;

        //Set CardView content
        taskTitle = (TextView) holder.mCardView.findViewById(R.id.task_title);
        taskProgression = (TextView) holder.mCardView.findViewById(R.id.task_progression);
        assignedPeople = (TextView) holder.mCardView.findViewById(R.id.task_assignee);
        dayLeft = (TextView) holder.mCardView.findViewById(R.id.task_time);

        layTags = (LinearLayout) holder.mCardView.findViewById(R.id.layout_tags);

        taskTitle.setText(currentContent.getTitle());
        assignedPeople.setText(String.format("%s people assigned", String.valueOf(currentContent.getUsers().size())));
        taskProgression.setText(String.format("Task progression : %s%%", String.valueOf(currentContent.getAccomplishedPercent())));
        long daysLeft = (currentContent.getStartDate().getTime() - currentContent.getEndDate().getTime()) / millisecondToDays;
        if (daysLeft > 1)
            dayLeft.setText(String.format("%s days left", String.valueOf(daysLeft)));
        else if (daysLeft > 0)
            dayLeft.setText(String.format("%s day left", String.valueOf(daysLeft)));
        else if (daysLeft < -1)
            dayLeft.setText(String.format("Finished since %s days", String.valueOf(Math.abs(daysLeft))));
        else
            dayLeft.setText(String.format("Finished since %s day", String.valueOf(Math.abs(daysLeft))));
        if (daysLeft <= 7)
            dayLeft.setTextColor(ContextCompat.getColor(holder.mCardView.getContext(), R.color.colorGrappboxRed));
        else
            dayLeft.setTextColor(ContextCompat.getColor(holder.mCardView.getContext(), R.color.Black));

        for (TaskTag tag : currentContent.getTags())
        {
            //Draw here tag
            Button newTag = new Button(layTags.getContext());
            newTag.setText("");
            newTag.setBackgroundColor(Color.parseColor("#202020")); //Set color here
            newTag.setMaxWidth(layTags.getWidth());
            layTags.addView(newTag);
        }

        holder.mCardView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (interaction != null)
                    interaction.onOpenClicked(currentContent.getId());
            }
        });
    }

    public void setDataSet(Task[] dataSet)
    {
        this.dataSet = dataSet;
    }

    @Override
    public int getItemCount() {
        return dataSet == null ? 0 : dataSet.length;
    }



    public static class ViewHolder extends RecyclerView.ViewHolder {
        // each data item is just a string in this case
        public CardView mCardView;
        public ViewHolder(CardView v) {
            super(v);
            mCardView = v;
        }

    }
}