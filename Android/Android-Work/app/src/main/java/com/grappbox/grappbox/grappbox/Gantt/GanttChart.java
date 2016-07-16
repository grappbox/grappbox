package com.grappbox.grappbox.grappbox.Gantt;

import android.annotation.TargetApi;
import android.content.Context;
import android.content.res.TypedArray;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Matrix;
import android.graphics.Paint;
import android.graphics.Path;
import android.os.Build;
import android.support.v4.content.ContextCompat;
import android.text.TextPaint;
import android.text.TextUtils;
import android.util.AttributeSet;
import android.util.Log;
import android.util.Pair;
import android.view.GestureDetector;
import android.view.MotionEvent;
import android.view.ScaleGestureDetector;
import android.view.View;

import com.grappbox.grappbox.grappbox.R;

import java.text.DateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.List;

/**
 * Created by wieser_m on 08/03/2016.
 */
public class GanttChart extends View {
    private final long millisecondToDays = 86400000;


    //Overall property
    private int backgroundColor;

    //Tasks parameters
    private List<Task> tasks;
    private int taskHeight;
    private int oneDayWidth;
    private int minYPos;

    //Tasklist parameters
    private int tasklistWidth;
    private int tasklistPadding;

    //Timeline parameters
    private int timelineHeight;
    private int mainDayLineHeight;
    private int otherDayLineHeight;

    //Brushes
    private Paint taskBrush;
    private Paint accomplishementBrush;
    private TextPaint textBrush;
    private Paint lineBrush;
    private Paint todayLineBrush;
    private Paint eraserBrush;
    private Paint dividerBrush;

    //Today Date
    private Date zeroDate;

    //Screen parameters
    private int screenWidth;

    //Gesture system
    private GestureDetector __INTERNAL_GESTURES__;
    private ScaleGestureDetector __INTERNAL_PINCH__;
    private float zoom;
    private float pan;
    private float panVertical;

    //Callbacks
    private GanttTaskListener taskListener;

    public GanttChart(Context context) {
        super(context);
        Init(null);
    }

    public GanttChart(Context context, AttributeSet attrs) {
        super(context, attrs);
        Init(attrs);
    }

    public GanttChart(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        Init(attrs);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public GanttChart(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
        Init(attrs);
    }

    private void Init(AttributeSet attrs){
        __INTERNAL_GESTURES__ = new GestureDetector(getContext(), new GanttScrollListener());
        __INTERNAL_PINCH__ = new ScaleGestureDetector(getContext(), new GanttPinchListener());
        backgroundColor = ContextCompat.getColor(getContext(), android.support.v7.appcompat.R.color.background_material_light);
        TypedArray typedArray = getContext().getTheme().obtainStyledAttributes(attrs, R.styleable.GanttChart,0,0);

        tasks = new ArrayList<>();
        zoom = 10;

        taskHeight = typedArray.getInteger(R.styleable.GanttChart_taskHeight, 200);
        tasklistPadding = typedArray.getInteger(R.styleable.GanttChart_taskListPadding, 100);
        timelineHeight = typedArray.getInteger(R.styleable.GanttChart_timelineHeight, 100);

        pan = 0;
        panVertical = 0;
        oneDayWidth = 50;
        mainDayLineHeight = 40;
        otherDayLineHeight = 20;
        tasklistWidth = 500;

        taskBrush = new Paint(Paint.ANTI_ALIAS_FLAG);
        taskBrush.setStyle(Paint.Style.FILL_AND_STROKE);
        taskBrush.setColor(typedArray.getColor(R.styleable.GanttChart_taskColor, Color.GRAY));

        textBrush = new TextPaint(Paint.ANTI_ALIAS_FLAG);
        textBrush.setStyle(Paint.Style.FILL);
        textBrush.setColor(typedArray.getColor(R.styleable.GanttChart_textColor, Color.BLACK));
        textBrush.setTextSize(typedArray.getInteger(R.styleable.GanttChart_textSize, 50));

        lineBrush = new Paint(Paint.ANTI_ALIAS_FLAG);
        lineBrush.setStyle(Paint.Style.FILL_AND_STROKE);
        lineBrush.setColor(typedArray.getColor(R.styleable.GanttChart_dependeciesLineColor, Color.BLACK));
        lineBrush.setStrokeWidth(typedArray.getInteger(R.styleable.GanttChart_strokeWidth, 2));

        todayLineBrush = new Paint(Paint.ANTI_ALIAS_FLAG);
        todayLineBrush.setStyle(Paint.Style.FILL_AND_STROKE);
        todayLineBrush.setColor(typedArray.getColor(R.styleable.GanttChart_todayLineColor, Color.RED));
        todayLineBrush.setStrokeWidth(typedArray.getInteger(R.styleable.GanttChart_strokeWidth, 2));

        eraserBrush = new Paint(Paint.ANTI_ALIAS_FLAG);
        eraserBrush.setStyle(Paint.Style.FILL);
        eraserBrush.setColor(typedArray.getColor(R.styleable.GanttChart_backgroundColor, backgroundColor));

        dividerBrush = new Paint(Paint.ANTI_ALIAS_FLAG);
        dividerBrush.setStyle(Paint.Style.FILL_AND_STROKE);
        dividerBrush.setColor(typedArray.getColor(R.styleable.GanttChart_dividerColor, Color.parseColor("#202020")));
        dividerBrush.setStrokeWidth(1);

        accomplishementBrush = new Paint(Paint.ANTI_ALIAS_FLAG);
        accomplishementBrush.setStyle(Paint.Style.FILL_AND_STROKE);
        accomplishementBrush.setColor(typedArray.getColor(R.styleable.GanttChart_accomplishementTaskColor, Color.parseColor("#70ad47")));

        Calendar cal = Calendar.getInstance();
        cal.set(Calendar.MILLISECOND, 0);
        cal.set(Calendar.SECOND, 0);
        cal.set(Calendar.MINUTE, 0);
        cal.set(Calendar.HOUR_OF_DAY, 0);
        zeroDate = cal.getTime();
        taskListener = null;
    }

    @Override
    protected void onSizeChanged(int w, int h, int oldw, int oldh) {
        super.onSizeChanged(w, h, oldw, oldh);
        screenWidth = w;
        minYPos = getPaddingTop();
        Date furtherDate = null;
        for (Task task : tasks)
        {
            float taskWidth = textBrush.measureText(task.getTitle());
            if (furtherDate == null || task.getEndDate().after(furtherDate))
                furtherDate = task.getEndDate();
            if (taskWidth > tasklistWidth)
                tasklistWidth = (int) taskWidth;
        }
        if (furtherDate != null)
            oneDayWidth = (w - tasklistWidth - tasklistPadding) / ((int)((furtherDate.getTime() - zeroDate.getTime()) / millisecondToDays));
        else
            oneDayWidth = (w - tasklistWidth - tasklistPadding) / 7;
    }

    public void SetTasks(List<Task> tasks)
    {
        this.tasks = tasks;
        invalidate();
    }

    public void setTaskListener(GanttTaskListener listener)
    {
        taskListener = listener;
    }

    private void drawTimeline(Canvas canvas)
    {
        float placeholderDateWidth = textBrush.measureText("00/00/0000");
        canvas.drawRect(0, 0, canvas.getWidth(), getPaddingTop() + timelineHeight, eraserBrush);
        int top = (int)(getPaddingTop());
        int left = (int)(getPaddingLeft() + tasklistWidth + tasklistPadding - (oneDayWidth * 7));
        int index = 0;
        int nextLeft = - (oneDayWidth * 7);
        float maxGanttWidth = screenWidth - left;
        DateFormat formater = DateFormat.getDateInstance(DateFormat.SHORT);
        Calendar zeroCal = Calendar.getInstance();
        zeroCal.setTime(zeroDate);
        Calendar cal = Calendar.getInstance();

        cal.setTime(zeroDate);
        cal.add(Calendar.DAY_OF_MONTH, -7);
        cal.add(Calendar.DAY_OF_MONTH, (int) -pan / oneDayWidth);
        while (left <= screenWidth * 2)
        {

            String text = formater.format(cal.getTime());
            boolean isToday = cal.get(Calendar.DAY_OF_MONTH) == zeroCal.get(Calendar.DAY_OF_MONTH) && cal.get(Calendar.MONTH) == zeroCal.get(Calendar.MONTH) && cal.get(Calendar.YEAR) == zeroCal.get(Calendar.YEAR);
            if (left >= nextLeft && ((oneDayWidth > placeholderDateWidth || (cal.get(Calendar.DAY_OF_MONTH) == 1 && oneDayWidth * 7 <= placeholderDateWidth) || (cal.get(Calendar.DAY_OF_WEEK) == Calendar.MONDAY && oneDayWidth * 7 > placeholderDateWidth + 20))))
            {
                canvas.drawText(text, left + (pan % oneDayWidth) , top + textBrush.getTextSize(), textBrush);
                nextLeft = (int) (left + textBrush.measureText(text));
            }
            if (cal.get(Calendar.DAY_OF_WEEK) == Calendar.MONDAY || isToday)
            {
                //Draw big bar
                canvas.drawLine(left + (pan % oneDayWidth), top + timelineHeight - mainDayLineHeight, left + (pan % oneDayWidth) + 2, top + timelineHeight, (isToday ? todayLineBrush : taskBrush));
            } else {
                //Draw little bar
                canvas.drawLine(left + (pan % oneDayWidth) , top + timelineHeight - otherDayLineHeight, left + (pan % oneDayWidth), top + timelineHeight, taskBrush);
            }
            left += oneDayWidth;
            ++index;
            cal.add(Calendar.DAY_OF_MONTH, 1);
        }
        canvas.drawRect(0,0,getPaddingLeft() + tasklistWidth + tasklistPadding, timelineHeight + getPaddingTop(), eraserBrush);
    }

    private void drawArrow(Canvas canvas, Pair<Integer, Integer> middleBase, Pair<Integer, Integer> endArrow)
    {
        Path path = new Path();
        int deltaX = endArrow.first - middleBase.first;
        int deltaY = endArrow.second - middleBase.second;
        if (deltaX != 0 && deltaY != 0)
        {
            Log.w("[GANTT CHART]", "DeltaX and Delta Y != 0, arrow angle not supported");
            return;
        }
        double distance = Math.sqrt(Math.pow(deltaY, 2) + Math.pow(deltaX,2));
        int baselength = (int) (distance / (Math.sqrt(3) / 2));
        float firstPointX = 0;
        float firstPointY = 0;

        if (deltaX != 0)
        {
            firstPointY = (float) (middleBase.second - (distance / 2));
            firstPointX = middleBase.first;
        }
        else
        {
            firstPointX = (float) (middleBase.first - (distance / 2));
            firstPointY = middleBase.second;
        }

        path.moveTo(firstPointX, firstPointY);
        path.lineTo(endArrow.first, endArrow.second);
        path.moveTo(firstPointX, firstPointY);
        if (deltaX != 0)
            path.rLineTo(0, baselength);
        else
            path.rLineTo(baselength, 0);
        path.lineTo(endArrow.first, endArrow.second);
        path.close();
        canvas.drawPath(path, taskBrush);
    }

    private void drawMilestone(Canvas canvas, Pair<Integer, Integer> startPosition)
    {
        Path diamond = new Path();
        int hauteur  = (startPosition.second + ((taskHeight / 2))) - startPosition.second;
        int baselength = (int) (hauteur / (Math.sqrt(3) / 2));

        diamond.moveTo(startPosition.first, startPosition.second);
        diamond.rLineTo(baselength / 2, hauteur / 2);
        diamond.rLineTo(-(baselength / 2), hauteur / 2);
        diamond.rLineTo(-(baselength / 2), -(hauteur / 2));
        diamond.rLineTo(baselength / 2, -(hauteur / 2));
        diamond.close();
        canvas.drawPath(diamond, taskBrush);
    }

    private void drawTasks(Canvas canvas)
    {
        int index = 0;
        GregorianCalendar cal = new GregorianCalendar();
        Date zeroDateDisplayed;

        cal.setTime(zeroDate);
        cal.add(Calendar.DAY_OF_MONTH, (int) -pan / oneDayWidth);
        zeroDateDisplayed = cal.getTime();
        for (Task task : tasks)
        {
            int daysOffset = (int) ((task.getStartDate().getTime() - zeroDate.getTime()) / millisecondToDays);
            int daysNumber = (int)((task.getEndDate().getTime() - task.getStartDate().getTime()) / millisecondToDays);
            float left;
            int top, right, bottom;

            if (task.getEndDate().getTime() < zeroDateDisplayed.getTime() && task.getLinks().size() == 0) {
                //Log.e("GANTT CHART", task.toString() + " skipped!");
                ++index;
                continue;
            }
            left = tasklistWidth + tasklistPadding + (oneDayWidth * daysOffset) + pan;
            top = (int) (getPaddingTop() + timelineHeight + minYPos + (taskHeight * index) + (taskHeight / 4) + panVertical);
            right = (int) (left + (oneDayWidth * daysNumber));
            bottom = top + (taskHeight / 2);
            if (left < tasklistWidth + tasklistPadding && right < tasklistWidth + tasklistPadding && task.getLinks().size() == 0)
            {
                //Log.e("GANTT CHART", task.toString() + " skipped!");
                canvas.drawText((String) TextUtils.ellipsize(task.getTitle(), textBrush, tasklistWidth, TextUtils.TruncateAt.END), left, top, textBrush);
                ++index;
                continue;
            }
            if (!task.IsMilestone() && !task.IsContainer())
            {
                float accomplishementWidth = (right - left) * (task.getAccomplishedPercent() / 100);
                canvas.drawRect(left, top, right, bottom, taskBrush);
                //Draw percent accomplishment
                if (accomplishementWidth > 0)
                    canvas.drawRect(left, bottom - ((bottom - top) / 4), left + accomplishementWidth,bottom, accomplishementBrush);
            }
            else if (task.IsMilestone())
                drawMilestone(canvas, new Pair<Integer, Integer>((int) left, top));
            else if (task.IsContainer())
            {
                //Draw container
                canvas.drawRect(left, top, right, bottom - (bottom - top) / 1.5f,taskBrush);
                Path triangle = new Path();
                triangle.moveTo(left - (oneDayWidth / 2), top);
                triangle.rLineTo(oneDayWidth, 0);
                triangle.rLineTo(-(oneDayWidth / 2), bottom - top);
                triangle.rLineTo(-(oneDayWidth / 2), top - bottom);
                triangle.close();
                canvas.drawPath(triangle, taskBrush);
                Matrix translate = new Matrix();
                translate.setTranslate(right - left - (oneDayWidth / 2), 0);
                triangle.transform(translate);
                canvas.drawPath(triangle, taskBrush);
            }
            for (Pair<String, Task.ELinkType> pair : task.getLinks())
            {
                //Pair<Integer, Integer> startPosition = new Pair<Integer, Integer>((int) left, top + (taskHeight / 4));

                Task endTask = null;
                int endIndex = 0;
                for (Task finder : tasks)
                {
                    if (finder.getId().equals(pair.first))
                    {
                        endTask = finder;
                        break;
                    }
                    ++endIndex;
                }
                if (endTask == null)
                    continue;
                int edaysOffset = (int) ((endTask.getStartDate().getTime() - zeroDate.getTime()) / millisecondToDays);
                float eleft = tasklistWidth + tasklistPadding + (oneDayWidth * edaysOffset) + pan;
                int etop = (int) (getPaddingTop() + timelineHeight + minYPos + (taskHeight * endIndex) + (taskHeight / 4) + panVertical);
                int edaysNumber = (int)((endTask.getEndDate().getTime() - endTask.getStartDate().getTime()) / millisecondToDays);
                Pair<Integer, Integer> startPosition = new Pair<Integer, Integer>((int)eleft, etop);
                drawLink(canvas, pair.second, startPosition, daysNumber, task, index, endIndex);
            }

            ++index;
        }
        canvas.drawRect(0,0, tasklistWidth + tasklistPadding, canvas.getHeight(), eraserBrush);
        index = 0;
        for (Task task : tasks)
        {
            float left = getPaddingLeft();
            int top = (int) (getPaddingTop() + timelineHeight + (taskHeight * index++) + (taskHeight / 2) + (textBrush.getTextSize() / 2) + panVertical);
            canvas.drawText((String) TextUtils.ellipsize(task.getTitle(), textBrush, tasklistWidth, TextUtils.TruncateAt.END), left, top, textBrush);
        }
    }

    private ArrayList<Float> drawStartToEndLink(Pair<Integer, Integer> startPosition, int endTaskIndex, int currentTaskIndex, int endTaskRight, Task endTask)
    {
        ArrayList<Float> lines = new ArrayList<>();

        lines.add(Float.valueOf(startPosition.first));
        lines.add((float) (startPosition.second));
        lines.add(lines.get(0) - oneDayWidth);
        lines.add(lines.get(1));

        lines.add(lines.get(2));
        lines.add(lines.get(3));
        lines.add(lines.get(4));
        lines.add(lines.get(5) + ((taskHeight / 2) * (endTaskIndex < currentTaskIndex ? -1 : 1)));

        lines.add(lines.get(6));
        lines.add(lines.get(7));
        lines.add(lines.get(8) + ((endTaskRight + oneDayWidth) - (startPosition.first - oneDayWidth)));
        lines.add(lines.get(9));

        lines.add(lines.get(10));
        lines.add(lines.get(11));
        lines.add(lines.get(12));
        lines.add(lines.get(13) + (((Math.abs(currentTaskIndex - endTaskIndex) - 1) * taskHeight + taskHeight / 2) * (endTaskIndex < currentTaskIndex ? -1 : 1)));

        lines.add(lines.get(14));
        lines.add(lines.get(15));
        lines.add(lines.get(16) - (endTask.IsMilestone() ? 0 : oneDayWidth));
        lines.add(lines.get(17));

        return lines;
    }

    private ArrayList<Float> drawEndToEndLink(Pair<Integer, Integer> startPosition, int startTaskDuration, int daysNumber, int currentTaskIndex, int endTaskIndex, Task endTask)
    {
        ArrayList<Float> lines = new ArrayList<>();

        lines.add((float) (startPosition.first + (oneDayWidth * startTaskDuration)));
        lines.add((float) (startPosition.second));
        lines.add(lines.get(0) + (( 1 + Math.abs(daysNumber - startTaskDuration)) * oneDayWidth));
        lines.add(lines.get(1));

        lines.add(lines.get(2));
        lines.add(lines.get(3));
        lines.add(lines.get(4));
        lines.add(lines.get(5) + (((Math.abs(currentTaskIndex - endTaskIndex)) * taskHeight) * (endTaskIndex < currentTaskIndex ? -1 : 1)));

        lines.add(lines.get(6));
        lines.add(lines.get(7));
        lines.add(lines.get(8) - (endTask.IsMilestone() ? 0 : oneDayWidth));
        lines.add(lines.get(9));

        return lines;
    }

    private ArrayList<Float> drawEndToStartLink(Pair<Integer, Integer> startPosition, int startTaskDuration, int daysNumber, int currentTaskIndex, int endTaskIndex, int endTaskLeft, Task endTask)
    {
        ArrayList<Float> lines = new ArrayList<>();

        lines.add((float) (startPosition.first + (oneDayWidth * startTaskDuration)));
        lines.add((float) (startPosition.second));
        if ((startPosition.first + startTaskDuration * oneDayWidth) - endTaskLeft == 0)
        {
            lines.add(lines.get(0) + oneDayWidth);
            lines.add(lines.get(1));

            lines.add(lines.get(2));
            lines.add(lines.get(3));
            lines.add(lines.get(4));
            lines.add(lines.get(5) + (taskHeight / 2) * (currentTaskIndex < endTaskIndex ? 1 : -1));

            lines.add(lines.get(6));
            lines.add(lines.get(7));
            lines.add(lines.get(8) - 2 * oneDayWidth);
            lines.add(lines.get(9));

            lines.add(lines.get(10));
            lines.add(lines.get(11));
            lines.add(lines.get(12));
            lines.add(lines.get(13) + (((Math.abs(currentTaskIndex - endTaskIndex) - 1) * taskHeight + taskHeight / 2) * (endTaskIndex < currentTaskIndex ? -1 : 1)));

            lines.add(lines.get(14));
            lines.add(lines.get(15));
            lines.add(lines.get(16) + (endTask.IsMilestone() ? 0 : oneDayWidth));
            lines.add(lines.get(17));
        }
        else
        {
            lines.add(lines.get(0) + ((Math.abs(daysNumber - startTaskDuration) - 1) * oneDayWidth));
            lines.add(lines.get(1));

            lines.add(lines.get(2));
            lines.add(lines.get(3));
            lines.add(lines.get(4));
            lines.add(lines.get(5) + (((Math.abs(currentTaskIndex - endTaskIndex)) * taskHeight) * (endTaskIndex < currentTaskIndex ? -1 : 1)));

            lines.add(lines.get(6));
            lines.add(lines.get(7));
            lines.add(lines.get(8) + (endTask.IsMilestone() ? 0 : oneDayWidth));
            lines.add(lines.get(9));
        }

        return lines;
    }

    private ArrayList<Float> drawStartToStartLink(Pair<Integer, Integer> startPosition, int currentTaskIndex, int endTaskIndex, int endTaskLeft, Task endTask)
    {
        ArrayList<Float> lines = new ArrayList<>();
        int hauteur  = (startPosition.second + ((taskHeight / 2))) - startPosition.second;
        int baselength = (int) (hauteur / (Math.sqrt(3) / 2));

        lines.add(Float.valueOf(startPosition.first));
        lines.add((float) (startPosition.second));
        lines.add(lines.get(0) - oneDayWidth);
        lines.add(lines.get(1));

        lines.add(lines.get(2));
        lines.add(lines.get(3));
        lines.add(lines.get(4));
        lines.add(lines.get(5) + (((Math.abs(currentTaskIndex - endTaskIndex)) * taskHeight) * (endTaskIndex < currentTaskIndex ? -1 : 1)));

        lines.add(lines.get(6));
        lines.add(lines.get(7));
        lines.add((float) endTaskLeft - (endTask.IsMilestone() ? baselength / 2 : 0));
        lines.add(lines.get(9));

        return lines;
    }

    private void drawLink(Canvas canvas, Task.ELinkType linkType, Pair<Integer, Integer> startPosition,int startTaskDuration, Task endTask, int endTaskIndex, int currentTaskIndex)
    {
        Path line = new Path();

        int daysOffset = (int) (((endTask.getStartDate().getTime() - zeroDate.getTime()) / millisecondToDays));
        int daysNumber = (int)((endTask.getEndDate().getTime() - (endTask.getStartDate().getTime() < zeroDate.getTime() ? zeroDate.getTime() : endTask.getStartDate().getTime())) / millisecondToDays);
        int endTaskTop = getPaddingTop() + timelineHeight + (taskHeight * endTaskIndex) + (taskHeight / 2);
        int endTaskLeft = (int) (tasklistWidth + tasklistPadding + (oneDayWidth * (daysOffset)) + pan);
        int endTaskRight = endTaskLeft + (oneDayWidth * daysNumber);
        int endTaskBottom = endTaskTop + (taskHeight / 2);

        if (linkType == Task.ELinkType.NONE)
            return;
        ArrayList<Float> lines = new ArrayList<>();
        Pair<Integer,Integer> middlePoint = new Pair<>(-1000, -1000);
        Pair<Integer, Integer> endPoint = new Pair<>(-1000, -1000);

        switch (linkType)
        {
            case START_TO_END:
                lines = drawStartToEndLink(startPosition, endTaskIndex, currentTaskIndex, endTaskRight, endTask);
                middlePoint = new Pair<Integer, Integer>((int)((float)(lines.get(18)) + (taskHeight / 2)), (int)((float)(lines.get(19))));
                endPoint = new Pair<>((int)((float)(lines.get(18))), (int)((float)(lines.get(19))));
                break;
            case END_TO_END:
                lines = drawEndToEndLink(startPosition, startTaskDuration, daysNumber, currentTaskIndex, endTaskIndex, endTask);
                middlePoint = new Pair<Integer, Integer>((int)((float)(lines.get(10)) + (taskHeight / 2)), (int)((float)(lines.get(11))));
                endPoint = new Pair<>((int)((float)(lines.get(10))), (int)((float)(lines.get(11))));
                break;
            case END_TO_START:
                lines = drawEndToStartLink(startPosition, startTaskDuration, daysNumber, currentTaskIndex, endTaskIndex, endTaskLeft, endTask);
                if ((startPosition.first + startTaskDuration * oneDayWidth) - endTaskLeft == 0) {
                    middlePoint = new Pair<Integer, Integer>((int)((float)(lines.get(18)) - (taskHeight / 2)), (int)((float)(lines.get(19))));
                    endPoint = new Pair<>((int)((float)(lines.get(18))), (int)((float)(lines.get(19))));
                }
                else
                {
                    middlePoint = new Pair<Integer, Integer>((int) ((float) (lines.get(10)) - (taskHeight / 2)), (int) ((float) (lines.get(11))));
                    endPoint = new Pair<>((int) ((float) (lines.get(10))), (int) ((float) (lines.get(11))));
                }
                break;
            case START_TO_START:
                lines = drawStartToStartLink(startPosition, currentTaskIndex, endTaskIndex, endTaskLeft, endTask);
                middlePoint = new Pair<Integer, Integer>((int) ((float) (lines.get(10)) - (taskHeight / 5)), (int) ((float) (lines.get(11))));
                endPoint = new Pair<>((int) ((float) (lines.get(10))), (int) ((float) (lines.get(11))));
                break;
            default:
                return;
        }
        float[] linesPoints = new float[lines.size()];
        int i = 0;
        for (Float point : lines)
            linesPoints[i++] = (point != null ? point : Float.NaN);
        canvas.drawLines(linesPoints, lineBrush);
        drawArrow(canvas, middlePoint, endPoint);
    }

    private void drawTaskModifier(Canvas canvas)
    {
        int left = getPaddingLeft() + tasklistWidth + tasklistPadding / 4;
        int right = left + tasklistPadding / 2;
        int top = getPaddingTop() + timelineHeight;
        int bottom = canvas.getHeight();

        canvas.drawRect(left, top, right, bottom, dividerBrush);
    }

    @Override
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);
        if (isInEditMode())
            return;

        if (oneDayWidth < 0)
            oneDayWidth = (canvas.getWidth() - tasklistWidth -  tasklistPadding) / 7;
        canvas.drawRect(0, 0, canvas.getWidth(), canvas.getHeight(), eraserBrush);
        drawTasks(canvas);
        drawTimeline(canvas);
        drawTaskModifier(canvas);
    }

    //Interactions

    @Override
    public boolean onTouchEvent(MotionEvent event)
    {
        boolean ret = __INTERNAL_GESTURES__.onTouchEvent(event);
        ret |= __INTERNAL_PINCH__.onTouchEvent(event);
        if (!ret)
            Log.e("[GANTT CHART]", "Gesture detectors failed in complex gesture detection, please do your own");
        return ret;
    }

    public int findIndexFromY(float y)
    {
        int index = -1;
        if (y >= getPaddingTop() + timelineHeight)
            index = (int)((y - (getPaddingTop() + timelineHeight)) / taskHeight);

        return (index >= tasks.size() ? -1 : index);
    }

    public boolean isClickingOnTask(int taskIndex, float x)
    {
        if (taskIndex == -1)
            return false;
        if (x < getPaddingLeft() + tasklistWidth)
            return true;
        /*
        Task task = tasks.get(taskIndex);
        int daysOffset = (int) ((task.getStartDate().getTime() - zeroDate.getTime()) / millisecondToDays);
        int daysNumber = (int)((task.getEndDate().getTime() - task.getStartDate().getTime()) / millisecondToDays);
        float left = tasklistWidth + tasklistPadding + (oneDayWidth * daysOffset) + pan;
        float right = left + (oneDayWidth * daysNumber);
        x += pan;
        return x >= left && x <= right;*/
        return false;
    }


    //Interfaces
    public interface GanttTaskListener
    {
        void onTaskClick(Task task);
    }


    //Event Classes
    private class GanttScrollListener extends GestureDetector.SimpleOnGestureListener
    {
        boolean isTaskWidthChanging = false;


        @Override
        public boolean onDown(MotionEvent e) {
            int leftBox = getPaddingLeft() + tasklistWidth;
            int rightBox = getPaddingLeft() + tasklistWidth + tasklistPadding;
            isTaskWidthChanging = e.getX() >= leftBox && e.getX() <= rightBox;
            return true;
        }

        @Override
        public boolean onSingleTapUp(MotionEvent e) {
            int taskIndex = findIndexFromY(e.getY());
            boolean isClicked = isClickingOnTask(taskIndex, e.getX());

            if (isClicked && taskListener != null)
                taskListener.onTaskClick(tasks.get(taskIndex));
            return true;
        }

        @Override
        public boolean onScroll(MotionEvent e1, MotionEvent e2, float distanceX, float distanceY) {

            super.onScroll(e1, e2, distanceX, distanceY);
            float numberDays = distanceX / oneDayWidth;

            Calendar changes = Calendar.getInstance();

            changes.setTime(zeroDate);
            changes.add(Calendar.DAY_OF_MONTH, (int)(numberDays));

            if (Math.abs(distanceX) < Math.abs(distanceY) && isTaskWidthChanging)
                return true;
            if (Math.abs(distanceX) > Math.abs(distanceY)) {
                if (isTaskWidthChanging && tasklistWidth -distanceX > getPaddingLeft() && tasklistWidth - distanceX < screenWidth - (tasklistPadding / 4))
                {
                    tasklistWidth -= distanceX;
                }
                else
                {
                    pan += -distanceX;
                }

            }
            else if (panVertical -distanceY > -((tasks.size() - 1) * taskHeight))
                panVertical += -distanceY;
            if (panVertical > 0)
                panVertical = 0;
            invalidate();
            return true;
        }
    }

    private class GanttPinchListener extends ScaleGestureDetector.SimpleOnScaleGestureListener
    {
        @Override
        public boolean onScale(ScaleGestureDetector detector) {
            oneDayWidth *= detector.getScaleFactor();
            if (oneDayWidth * 7 < textBrush.measureText("00/00/0000"))
                oneDayWidth = (int) ((textBrush.measureText("00/00/0000")) / 7);
            else if (oneDayWidth * 7 >= screenWidth - getPaddingLeft() - tasklistPadding - tasklistWidth)
                oneDayWidth = (screenWidth - getPaddingLeft() - tasklistPadding - tasklistWidth) / 7;
            invalidate();
            return true;
        }
    }
}
