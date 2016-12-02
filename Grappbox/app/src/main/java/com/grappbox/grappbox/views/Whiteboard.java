package com.grappbox.grappbox.views;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.Rect;
import android.graphics.RectF;
import android.graphics.Typeface;
import android.os.Build;
import android.renderscript.Double2;
import android.renderscript.Float2;
import android.support.annotation.RequiresApi;
import android.support.compat.BuildConfig;
import android.util.AttributeSet;
import android.util.Log;
import android.view.GestureDetector;
import android.view.MotionEvent;
import android.view.View;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Marc Wieser the 30/11/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

/**
 * The Whiteboard give you an area where you can draw with
 * specific tools.
 * @see Tool
 */
public class Whiteboard extends View {
    private static final String LOG_TAG = Whiteboard.class.getSimpleName();

    public interface Callbacks{
        void onNewObject(JSONObject object);
        void onDeleteArea(JSONObject object);
    }

    /**
     * Describe the tools available to draw inside this whiteboard.
     * Possible values
     * <li>{@link #E_MOVE}</li>
     * <li>{@link #E_LINE}</li>
     * <li>{@link #E_HANDWRITE}</li>
     * <li>{@link #E_ELLIPSIS}</li>
     * <li>{@link #E_RECTANGLE}</li>
     * <li>{@link #E_DIAMOND}</li>
     * <li>{@link #E_TEXT}</li>
     */
    public enum Tool{
        E_MOVE,
        E_LINE,
        E_HANDWRITE,
        E_ELLIPSIS,
        E_RECTANGLE,
        E_DIAMOND,
        E_TEXT
    }

    /**
     * Describe a basic and incomplete object.
     * @see ShapeModel
     * @see LineModel
     * @see TextModel
     * @see HandwriteModel
     * @see EllipsisModel
     * @see RectangleModel
     * @see DiamondModel
     */
    private abstract class ObjectModel{
        public Tool drawingTool;
        public int strokeColor;

        /**
         * Constructor
         * @param type The tool the object have to use to draw itself
         */
        public ObjectModel(Tool type) {
            drawingTool = type;
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        public void init(JSONObject object) throws JSONException{
            String color = object.isNull("color") ? "" : object.getString("color");
            strokeColor = object.isNull("color") ? -1 : Color.parseColor((color.startsWith("#") ? "" : "#") + color);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        public abstract void draw(Canvas canvas);
    }

    /**
     * Describe a basic and incomplete shape.
     * Complete it by extending it, but don't use it by itself,
     * please use followings objects instead :
     * @see LineModel
     * @see TextModel
     * @see EllipsisModel
     * @see RectangleModel
     * @see DiamondModel
     * @see ObjectModel
     */
    private abstract class ShapeModel extends ObjectModel{
        public int backgroundColor;
        public Float2 positionStart;
        public Float2 positionEnd;
        public int lineWeight;

        /**
         * Constructor
         * @param type The tool the object have to use to draw itself
         */
        public ShapeModel(Tool type) {
            super(type);
        }

        /**
         * Calculate the real position on screen based on virtual canvas camera position
         * @return A float rectangle which describe the real position on screen. It can be negative or exceed screen size
         */
        public RectF getPosition(){
            return new RectF(mScreenPosition.x - positionStart.x, mScreenPosition.y - positionStart.y,
                            mScreenPosition.x - positionEnd.x,mScreenPosition.y - positionEnd.y);
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONObject startObject = object.getJSONObject("positionStart");
            JSONObject endObject = object.getJSONObject("positionEnd");

            super.init(object);
            String bgColor = object.isNull("background") ? "" : object.getString("background");
            backgroundColor = object.isNull("background") ? -1 : Color.parseColor((bgColor.startsWith("#") ? ""  : "#") + bgColor);
            positionStart = new Float2((float)startObject.getDouble("x"), (float)startObject.getDouble("y"));
            positionEnd = new Float2((float)endObject.getDouble("x"), (float)endObject.getDouble("y"));
            lineWeight = object.has("lineWeight") && !object.isNull("lineWeight") ? object.getInt("lineWeight") :  0;
        }
    }

    /**
     * Describe a complete text object into the whiteboard
     * @see ObjectModel
     */
    private class TextModel extends ObjectModel{
        public Float2 positionStart;
        public String text;
        public int size;
        public boolean isItalic;
        public boolean isBold;

        /**
         * Constructor
         */
        public TextModel() {
            super(Tool.E_TEXT);
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONObject startObject = object.getJSONObject("positionStart");

            super.init(object);
            positionStart = new Float2((float)startObject.getDouble("x"), (float)startObject.getDouble("y"));
            text = object.getString("text");
            size = object.getInt("size");
            isItalic = object.getBoolean("isItalic");
            isBold = object.getBoolean("isBold");
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            if (strokeColor == -1)
                return;
            mTextPainter.setColor(strokeColor);
            mTextPainter.setTextSize(size);
            if (isItalic){
                mTextPainter.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.ITALIC));
            }
            mTextPainter.setFakeBoldText(isBold);
            canvas.drawText(text, positionStart.x, positionStart.y, mTextPainter);
        }
    }

    /**
     * Describe a complete path from handwriting object into the whiteboard
     * Handwriting is a multiple mini segment drawing, described in Android Framework with Path object
     * @see ObjectModel
     */
    private class HandwriteModel extends ObjectModel{
        public int lineWeight;
        public List<Float2> points;

        /**
         * Constructor
         */
        public HandwriteModel() {
            super(Tool.E_HANDWRITE);
            points = new ArrayList<>();
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONArray pointsObject = object.getJSONArray("points");

            super.init(object);
            lineWeight = object.has("lineWeight") && !object.isNull("lineWeight") ? object.getInt("lineWeight") : 0;
            points.clear();
            for (int i = 0; i < pointsObject.length(); ++i){
                JSONObject pointObject = pointsObject.getJSONObject(i);
                points.add(new Float2((float)pointObject.getDouble("x"), (float)pointObject.getDouble("y")));
            }
        }

        /**
         * Calculate the real position on screen based on virtual canvas camera position
         * @param point The point to process
         * @return A float pair which describe the real point position on screen. It can be negative or exceed screen size
         */
        public Float2 getPosition(Float2 point){
            return new Float2(mScreenPosition.x - point.x, mScreenPosition.y - point.y);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            if (lineWeight > 0 && strokeColor != -1){
                mStrokePainter.setColor(strokeColor);
                mStrokePainter.setStrokeWidth(lineWeight);
                Path handwrite = new Path();
                for (Float2 point : points) {
                    Float2 position = getPosition(point);
                    if (point.equals(points.get(0))){
                        handwrite.moveTo(position.x, position.y);
                    } else {
                        handwrite.lineTo(position.x, position.y);
                    }
                }
                canvas.drawPath(handwrite, mStrokePainter);
            }
        }
    }

    /**
     * Describe a complete ellipsis object into the whiteboard
     * @see ObjectModel
     */
    private class EllipsisModel extends ShapeModel{
        public Double2 radius;

        /**
         * Constructor
         */
        public EllipsisModel() {
            super(Tool.E_ELLIPSIS);
        }

        /**
         * Init the object member variables with the input
         * @param object The object described in JSON formatted as in the <a href="https://goo.gl/kFmext">following document</a>
         * @throws JSONException
         */
        @Override
        public void init(JSONObject object) throws JSONException {
            JSONObject radiusObject = object.getJSONObject("radius");

            super.init(object);
            radius = new Double2(radiusObject.getDouble("x"), radiusObject.getDouble("y"));
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            if (backgroundColor != -1){
                mPainter.setColor(backgroundColor);
                canvas.drawOval(getPosition(), mPainter);
            }
            if (lineWeight > 0 && strokeColor != -1){
                mStrokePainter.setStrokeWidth(lineWeight);
                mStrokePainter.setColor(strokeColor);
                canvas.drawOval(getPosition(), mStrokePainter);
            }
        }
    }

    /**
     * Describe a complete line object into the whiteboard
     * @see ObjectModel
     */
    private class LineModel extends ShapeModel{

        /**
         * Constructor
         */
        public LineModel() {
            super(Tool.E_LINE);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            RectF position = getPosition();
            if (lineWeight > 0 && strokeColor != -1){
                mStrokePainter.setColor(strokeColor);
                mStrokePainter.setStrokeWidth(lineWeight);
                canvas.drawLine(position.left, position.top, position.right, position.bottom, mStrokePainter);
            }
        }
    }

    /**
     * Describe a complete rectangle object into the whiteboard
     * @see ObjectModel
     */
    private class RectangleModel extends ShapeModel{

        /**
         * Constructor
         */
        public RectangleModel() {
            super(Tool.E_RECTANGLE);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            if (backgroundColor != -1){
                mPainter.setColor(backgroundColor);
                canvas.drawRect(getPosition(), mPainter);
            }
            if (lineWeight > 0 && strokeColor != -1){
                mStrokePainter.setColor(strokeColor);
                mStrokePainter.setStrokeWidth(lineWeight);
                canvas.drawRect(getPosition(), mStrokePainter);
            }

        }
    }

    /**
     * Describe a complete diamond object into the whiteboard
     * @see ObjectModel
     */
    private class DiamondModel extends ShapeModel{

        /**
         * Constructor
         */
        public DiamondModel() {
            super(Tool.E_DIAMOND);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            Path shape = new Path();
            RectF position = getPosition();

            shape.moveTo(position.left + ((position.right - position.left) / 2), position.top);
            shape.lineTo(position.left, position.top + ((position.bottom - position.top) / 2));
            shape.lineTo(position.left + ((position.right - position.left) / 2), position.bottom);
            shape.lineTo(position.right, position.top + ((position.bottom - position.top) / 2));
            if (backgroundColor != -1){
                mPainter.setColor(backgroundColor);
                canvas.drawPath(shape, mPainter);
            }
            if (lineWeight > 0){
                mStrokePainter.setColor(strokeColor);
                mStrokePainter.setStrokeWidth(lineWeight);
                canvas.drawPath(shape, mStrokePainter);
            }
        }
    }

    private Tool mCurrentTool;
    private Float2 mScreenPosition;
    private Float2 mMaxPosition;
    private List<ObjectModel> mObjects;
    private List<Callbacks> mCallbacks;
    private Paint mPainter, mTextPainter, mStrokePainter;
    private GestureDetector mGestures;

    /**
     * Constructor
     * @param context The context object, it's generally the activity that contain this View
     */
    public Whiteboard(Context context) {
        super(context);
        setup(context);
    }

    public Whiteboard(Context context, AttributeSet attrs) {
        super(context, attrs);
        setup(context);
    }

    public Whiteboard(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        setup(context);
    }

    @RequiresApi(api = Build.VERSION_CODES.LOLLIPOP)
    public Whiteboard(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
        setup(context);
    }

    private void setup(Context context){
        mCurrentTool = Tool.E_MOVE;
        mObjects = new ArrayList<>();
        mCallbacks = new ArrayList<>();
        mPainter = new Paint(Paint.ANTI_ALIAS_FLAG);
        mPainter.setStyle(Paint.Style.FILL);
        mTextPainter = new Paint(Paint.ANTI_ALIAS_FLAG);
        mStrokePainter = new Paint(Paint.ANTI_ALIAS_FLAG);
        mStrokePainter.setStyle(Paint.Style.STROKE);
        mScreenPosition = new Float2(0, 0);
        mMaxPosition = new Float2(getWidth(), getHeight());
        mGestures = new GestureDetector(context, new EventDetector());
    }

    /**
     * Change the current tool to user
     * @param type The tool's type to set in the whiteboard
     */
    public void setTool(Tool type){
        mCurrentTool = type;
    }

    /**
     * Register a new listener on whiteboard events if it doesn't exist yet
     * @param listener The implementation of all whiteboard events' reactions
     * @see Whiteboard#unregisterListener(Callbacks)
     */
    public void registerListener(Callbacks listener){
        if (mCallbacks.contains(listener))
            return;
        mCallbacks.add(listener);
    }

    /**
     * Delete a listener on whiteboard events that was registered before
     * @param listener The implementation of all whiteboard events' reactions
     * @see Whiteboard#registerListener(Callbacks)
     */
    public void unregisterListener(Callbacks listener){
        mCallbacks.remove(listener);
    }

    public void clear(){
        clear(true);
    }

    public void clear(boolean invalidateView){
        mObjects.clear();
        if (invalidateView)
            postInvalidate();
    }

    /**
     * Create automatically the good object model from the type key
     * @param type This define the object's type we wan't to create
     * @return A valid object model, with the tool automatically inferred thanks to the type
     * @throws UnsupportedOperationException This function throw when an invalid object type is given as input
     * @see ObjectModel
     */
    private ObjectModel createObjectModel(String type) throws UnsupportedOperationException{
        ObjectModel object;

        switch (type){
            case "LINE":
                object = new LineModel();
                break;
            case "HANDWRITE":
                object = new HandwriteModel();
                break;
            case "ELLIPSE":
                object = new EllipsisModel();
                break;
            case "RECTANGLE":
                object = new RectangleModel();
                break;
            case "DIAMOND":
                object = new DiamondModel();
                break;
            case "TEXT":
                object = new TextModel();
                break;
            default:
                throw new UnsupportedOperationException("Invalid type");
        }
        return object;
    }

    /**
     * Add objects to the current whiteboard
     * @param objects An array of JSON objects as described in the <a href="https://goo.gl/kFmext">following document</a>
     * @throws JSONException
     */
    public void feed(JSONArray objects) throws JSONException{
        if (objects == null)
            return;
        Log.e(LOG_TAG, objects.toString());
        for (int i = 0; i < objects.length(); ++i){
            JSONObject current = objects.getJSONObject(i);
            ObjectModel object = createObjectModel(current.getString("type"));
            object.init(current);
            mObjects.add(object);
        }
        postInvalidate();
    }

    @Override
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);

        for (ObjectModel object : mObjects) {
            object.draw(canvas);
        }
    }

    class EventDetector extends GestureDetector.SimpleOnGestureListener{
        @Override
        public boolean onDown(MotionEvent e) {
            return true;
        }

        @Override
        public boolean onFling(MotionEvent e1, MotionEvent e2, float velocityX, float velocityY) {
            if (mCurrentTool != Tool.E_MOVE){
                return true;
            }
            mScreenPosition.x += velocityX;
            mScreenPosition.y += velocityY;
            if (mScreenPosition.x > mMaxPosition.x)
                mScreenPosition.x = mMaxPosition.x;
            if (mScreenPosition.y > mMaxPosition.y)
                mScreenPosition.y = mMaxPosition.y;
            postInvalidate();
            return true;
        }
    }
}
