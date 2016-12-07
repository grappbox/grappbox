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
        E_TEXT,
        E_ERASER
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
        public String id;
        public boolean isSent;

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
            strokeColor = object.isNull("color") ? Integer.MAX_VALUE : Color.parseColor((color.startsWith("#") ? "" : "#") + color);
            id = object.getString("id");
            isSent = true;
        }

        public void init(int strokeColor){
            this.strokeColor = strokeColor;
            isSent = false;
        }

        public JSONObject toJSON() throws JSONException {
            JSONObject json = new JSONObject();

            if (drawingTool == Tool.E_DIAMOND)
                json.put("type", "DIAMOND");
            else if (drawingTool == Tool.E_ELLIPSIS)
                json.put("type", "ELLIPSE");
            else if (drawingTool == Tool.E_HANDWRITE)
                json.put("type", "HANDWRITE");
            else if (drawingTool == Tool.E_LINE)
                json.put("type", "LINE");
            else if (drawingTool == Tool.E_RECTANGLE)
                json.put("type", "RECTANGLE");
            else if (drawingTool == Tool.E_TEXT)
                json.put("type", "TEXT");
            json.put("color", strokeColor == Integer.MAX_VALUE ? null : colorToString(strokeColor));

            return json;
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
            float tmp;
            RectF ret = new RectF(positionStart.x - mScreenPosition.x,positionStart.y - mScreenPosition.y,
                    positionEnd.x - mScreenPosition.x, positionEnd.y - mScreenPosition.y);
            if (ret.right < ret.left){
                tmp = ret.right;
                ret.right = ret.left;
                ret.left = tmp;
            }
            if (ret.bottom < ret.top){
                tmp = ret.bottom;
                ret.bottom = ret.top;
                ret.top = tmp;
            }
            return ret;
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
            backgroundColor = object.isNull("background") ? Integer.MAX_VALUE : Color.parseColor((bgColor.startsWith("#") ? ""  : "#") + bgColor);
            positionStart = new Float2((float)startObject.getDouble("x"), (float)startObject.getDouble("y"));
            positionEnd = new Float2((float)endObject.getDouble("x"), (float)endObject.getDouble("y"));
            lineWeight = object.has("lineWeight") && !object.isNull("lineWeight") ? object.getInt("lineWeight") :  0;
        }

        public void setPositionEnd(Float2 posEnd){
            positionEnd = posEnd;
        }

        public void init(int backgroundColor, Float2 positionStart, Float2 positionEnd, int strokeColor, int lineWeight) {
            super.init(strokeColor);
            this.backgroundColor = backgroundColor;
            this.positionStart = positionStart;
            this.positionEnd = positionEnd;
            this.lineWeight = lineWeight;
        }

        @Override
        public JSONObject toJSON() throws JSONException {
            JSONObject json = super.toJSON();
            JSONObject jpositionStart = new JSONObject(), jpositionEnd = new JSONObject();

            json.put("background", backgroundColor == Integer.MAX_VALUE ? null : colorToString(backgroundColor));
            jpositionStart.put("x", positionStart.x);
            jpositionStart.put("y", positionStart.y);
            jpositionEnd.put("x", positionEnd.x);
            jpositionEnd.put("y", positionEnd.y);
            json.put("positionStart", jpositionStart);
            json.put("positionEnd", jpositionEnd);
            json.put("lineWeight", lineWeight);
            return json;
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

        public void init(int color, String text, int size, Float2 positionStart, boolean isItalic, boolean isBold){
            super.init(color);
            this.positionStart = positionStart;
            this.text = text;
            this.size = size;
            this.isItalic = isItalic;
            this.isBold = isBold;
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
            mTextPainter.setTextSize((float) (size * 1.333333));
            if (isItalic){
                mTextPainter.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.ITALIC));
            }
            mTextPainter.setFakeBoldText(isBold);
            canvas.drawText(text, positionStart.x - mScreenPosition.x, positionStart.y - mScreenPosition.y, mTextPainter);
        }

        @Override
        public JSONObject toJSON() throws JSONException {
            JSONObject json = super.toJSON();
            JSONObject jpositionStart = new JSONObject(), jpositionEnd = new JSONObject();

            float width = mTextPainter.measureText(text);
            Paint.FontMetrics metrics = mTextPainter.getFontMetrics();
            float height = metrics.bottom - metrics.top;
            jpositionStart.put("x", positionStart.x);
            jpositionStart.put("y", positionStart.y);
            jpositionEnd.put("x", positionStart.x + width);
            jpositionEnd.put("y", positionStart.y + height);
            json.put("positionStart", jpositionStart);
            json.put("positionEnd", jpositionEnd);
            json.put("text", text);
            json.put("size", size);
            json.put("isItalic", isItalic);
            json.put("isBold", isBold);

            return json;
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

        public void init(int strokeColor, int lineWeight, List<Float2> points){
            super.init(strokeColor);
            this.lineWeight = lineWeight;
            this.points = points;
        }

        /**
         * Calculate the real position on screen based on virtual canvas camera position
         * @param point The point to process
         * @return A float pair which describe the real point position on screen. It can be negative or exceed screen size
         */
        public Float2 getPosition(Float2 point){
            return new Float2(point.x - mScreenPosition.x, point.y - mScreenPosition.y);
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            if (lineWeight > 0 && strokeColor != Integer.MAX_VALUE){
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

        @Override
        public JSONObject toJSON() throws JSONException {
            JSONObject json = super.toJSON();
            JSONArray pointsArr = new JSONArray();

            for (Float2 point : points){
                JSONObject pos = new JSONObject();
                pos.put("x", point.x);
                pos.put("y", point.y);
                pointsArr.put(pos);
            }

            json.put("lineWeight", lineWeight);
            json.put("points", pointsArr);

            return json;
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
            if (backgroundColor != Integer.MAX_VALUE){
                mPainter.setColor(backgroundColor);
                canvas.drawOval(getPosition(), mPainter);
            }
            if (lineWeight > 0 && strokeColor != Integer.MAX_VALUE){
                mStrokePainter.setStrokeWidth(lineWeight);
                mStrokePainter.setColor(strokeColor);
                canvas.drawOval(getPosition(), mStrokePainter);
            }
        }

        @Override
        public JSONObject toJSON() throws JSONException {
            JSONObject json = super.toJSON();
            JSONObject jradius = new JSONObject();

            RectF pos = getPosition();
            float width = pos.right - pos.left;
            float height = pos.bottom - pos.top;
            jradius.put("x", width / 2);
            jradius.put("y", height / 2);
            json.put("radius", jradius);

            return json;
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

        @Override
        public RectF getPosition() {
            RectF ret = new RectF(positionStart.x - mScreenPosition.x,positionStart.y - mScreenPosition.y,
                    positionEnd.x - mScreenPosition.x, positionEnd.y - mScreenPosition.y);
            return ret;
        }

        /**
         * Event to call when we need to draw the object
         * @param canvas The canvas given by the whiteboard view to draw on
         */
        @Override
        public void draw(Canvas canvas) {
            RectF position = getPosition();
            if (lineWeight > 0 && strokeColor != Integer.MAX_VALUE){
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
            if (backgroundColor != Integer.MAX_VALUE){
                mPainter.setColor(backgroundColor);
                canvas.drawRect(getPosition(), mPainter);
            }
            if (lineWeight > 0 && strokeColor != Integer.MAX_VALUE){
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
            shape.lineTo(position.left + ((position.right - position.left) / 2), position.top);
            if (backgroundColor != Integer.MAX_VALUE){
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

    private ObjectModel currentDraw = null;
    private int currentBackgroundColor = Integer.MAX_VALUE, currentLineColor = Integer.MAX_VALUE, currentSize = 0, currentLineWeight = 0;
    private String currentText = "";
    private boolean currentItalic = false, currentBold = false;

    private int stringToColor(String color){
        return color == null || color.isEmpty() ? Integer.MAX_VALUE : Color.parseColor((color.startsWith("#") ? ""  : "#") + color);
    }

    public void setCurrentShapeSettings(String background, String stroke, int lineWeight, Tool tool){
        mCurrentTool = tool;
        currentBackgroundColor = stringToColor(background);
        currentLineColor = stringToColor(stroke);
        currentLineWeight = lineWeight;
    }

    public void setCurrentTextSettings(String color, int currentSize, String text, boolean isItalic, boolean isBold){
        currentLineColor = stringToColor(color);
        this.currentSize = currentSize;
        this.currentText = text;
        this.currentItalic = isItalic;
        this.currentBold = isBold;
        mCurrentTool = Tool.E_TEXT;
    }

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
        mMaxPosition = new Float2(4096, 2160);
        mGestures = new GestureDetector(context, new EventDetector());
    }

    @Override
    protected void onMeasure(int widthMeasureSpec, int heightMeasureSpec) {
        super.onMeasure(widthMeasureSpec, heightMeasureSpec);
        mMaxPosition.x = 4096 - getWidth() / 2;
        mMaxPosition.y = 2160 - getHeight() / 2;
        if (mMaxPosition.x < 0)
            mMaxPosition.x = 0;
        if (mMaxPosition.y < 0)
            mMaxPosition.y = 0;
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
        for (int i = 0; i < objects.length(); ++i){
            JSONObject current = objects.getJSONObject(i);
            ObjectModel object = createObjectModel(current.getString("type"));
            object.init(current);
            mObjects.add(object);
        }
        postInvalidate();
    }

    public void deleteObject(String id){
        for(ObjectModel model : mObjects){
            if (model.id.equals(id)){
                mObjects.remove(model);
                break;
            }
        }
    }

    @Override
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);

        canvas.scale(2, 2);
        for (ObjectModel object : mObjects) {
            object.draw(canvas);
        }
        if (currentDraw != null)
            currentDraw.draw(canvas);
    }

    private String colorToString(int color){
        return String.format("#%06X", 0xFFFFFF & color);
    }

    @Override
    public boolean onTouchEvent(MotionEvent event) {
        if (event.getAction() == MotionEvent.ACTION_UP && mCurrentTool != Tool.E_MOVE){
            if (mCurrentTool == Tool.E_TEXT){
                Float2 pos = new Float2(event.getX() / 2 + mScreenPosition.x, event.getY() / 2 + mScreenPosition.y);
                TextModel model = new TextModel();
                model.init(currentLineColor, currentText,currentSize, pos, currentItalic, currentBold);
                currentDraw = model;
            }
             else if (mCurrentTool == Tool.E_ERASER){
                JSONObject erase = new JSONObject();
                JSONObject center = new JSONObject();
                try {
                    center.put("x", event.getX()/2 + mScreenPosition.x);
                    center.put("y", event.getY()/2 + mScreenPosition.y);
                    erase.put("center", center);
                    erase.put("radius", "8");
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                for (Callbacks callback : mCallbacks){
                    if (callback == null)
                        continue;
                    callback.onDeleteArea(erase);
                }
            } else {
                currentDraw.isSent = true;
                for (Callbacks callback : mCallbacks){
                    if (callback == null)
                        continue;
                    try {
                        callback.onNewObject(currentDraw.toJSON());
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }
            }
        }
        return mGestures.onTouchEvent(event);
    }

    public boolean isShapeTool(Tool tool){
        return (tool == Tool.E_DIAMOND || tool == Tool.E_ELLIPSIS || tool == Tool.E_LINE || tool == Tool.E_RECTANGLE);
    }

    public boolean isDrawingTool(Tool tool){
        return (isShapeTool(tool) || tool == Tool.E_HANDWRITE || tool == Tool.E_TEXT);
    }

    class EventDetector extends GestureDetector.SimpleOnGestureListener{

        @Override
        public boolean onDown(MotionEvent e) {
            Float2 pos = new Float2(e.getX() / 2 + mScreenPosition.x, e.getY() / 2 + mScreenPosition.y);
            Log.e("TEST", "On Down : " + mCurrentTool);
            if (isShapeTool(mCurrentTool)){

                if (mCurrentTool == Tool.E_DIAMOND) {
                    currentDraw = new DiamondModel();
                } else if (mCurrentTool == Tool.E_LINE) {
                    currentDraw = new LineModel();
                } else if (mCurrentTool == Tool.E_RECTANGLE) {
                    currentDraw = new RectangleModel();
                } else {
                    currentDraw = new EllipsisModel();
                }

                ((ShapeModel)currentDraw).init(currentBackgroundColor, pos, pos, currentLineColor, currentLineWeight);
            } else if (mCurrentTool == Tool.E_HANDWRITE){
                HandwriteModel model = new HandwriteModel();

                model.init(currentLineColor, currentLineWeight, new ArrayList<Float2>());
                model.points.add(new Float2(e.getX() / 2 + mScreenPosition.x, e.getY() / 2 + mScreenPosition.y));
                currentDraw = model;
            }
            return true;
        }

        @Override
        public boolean onScroll(MotionEvent e1, MotionEvent e2, float distanceX, float distanceY) {
            if (mCurrentTool == Tool.E_MOVE){
                mScreenPosition.x += distanceX / 2;
                mScreenPosition.y += distanceY / 2;
                if (mScreenPosition.x < 0)
                    mScreenPosition.x = 0;
                if (mScreenPosition.y < 0)
                    mScreenPosition.y = 0;
                if (mScreenPosition.x > mMaxPosition.x)
                    mScreenPosition.x = mMaxPosition.x;
                if (mScreenPosition.y > mMaxPosition.y)
                    mScreenPosition.y = mMaxPosition.y;
            } else if (mCurrentTool == Tool.E_ERASER){
                return true;
            } else if (currentDraw instanceof ShapeModel){
                ((ShapeModel) currentDraw).setPositionEnd(new Float2(e2.getX() / 2 + mScreenPosition.x, e2.getY() / 2 + mScreenPosition.y));
            } else if (currentDraw instanceof HandwriteModel){
                ((HandwriteModel) currentDraw).points.add(new Float2(e2.getX() / 2 + mScreenPosition.x, e2.getY() / 2 + mScreenPosition.y));
            }
            postInvalidate();
            return true;
        }
    }
}
