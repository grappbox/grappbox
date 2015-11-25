package com.grappbox.grappbox.grappbox;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.RectF;
import android.util.AttributeSet;
import android.view.MotionEvent;
import android.view.View;

/**
 * Created by Arkanice on 25/11/2015.
 */
public class DrawingView extends View {

    private Path _DrawPath;
    private Paint _DrawPaint;
    private Paint _CanvasPaint;
    private Paint _DrawText;
    private int _PaintColor = 0xFF333333;
    private Canvas _DrawCanvas;
    private Bitmap _CanvasBitmap;
    private Bitmap _WhiteboardBitmap;

    private float touchXStart = 0;
    private float touchYStart = 0;
    private int _ShapeType = 0;

    private boolean _OnDraw = false;

    public DrawingView(Context context, AttributeSet attrs)
    {
        super(context, attrs);
        setupDraw();
    }

    private void setupDraw()
    {
        _DrawPaint = new Paint();
        _DrawPath = new Path();

        _DrawPaint.setColor(_PaintColor);
        _DrawPaint.setAntiAlias(true);
        _DrawPaint.setStrokeWidth(10);
        _DrawPaint.setStyle(Paint.Style.FILL);
        _DrawPaint.setStrokeJoin(Paint.Join.BEVEL);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);

        _DrawText = new Paint();
        _DrawText.setColor(_PaintColor);
        _DrawText.setTextSize(50);

        _CanvasPaint = new Paint(Paint.DITHER_FLAG);
    }

    @Override
    protected void onSizeChanged(int w, int h, int oldw, int oldh)
    {
        super.onSizeChanged(w, h, oldw, oldh);
        _CanvasBitmap = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888);
        _DrawCanvas = new Canvas(_CanvasBitmap);
    }

    @Override
    protected void onDraw(Canvas canvas)
    {
//        canvas.drawBitmap(_WhiteboardBitmap, new Rect(0, 0, 4096, 2160), new RectF(0, 0, 100, 100), _CanvasPaint);
        if (_OnDraw)
        {
            canvas.drawBitmap(_CanvasBitmap, 0, 0, _CanvasPaint);
            canvas.drawPath(_DrawPath, _DrawPaint);
            _CanvasBitmap = _WhiteboardBitmap;
        }
        else
        {
            canvas.drawBitmap(_CanvasBitmap, 0, 0, _CanvasPaint);
            canvas.drawPath(_DrawPath, _DrawPaint);
        }
    }


    @Override
    public boolean onTouchEvent(MotionEvent event)
    {
        float touchX = event.getX();
        float touchY = event.getY();

        switch (event.getAction())
        {
            case MotionEvent.ACTION_DOWN:
                _OnDraw = true;
                _WhiteboardBitmap = _CanvasBitmap;
                touchXStart = event.getX();
                touchYStart = event.getY();
                _DrawPath.moveTo(touchX, touchY);
                if (_ShapeType == 3) {
                    _DrawCanvas.drawText("TOTO", touchXStart, touchYStart, _DrawText);
                }

                break;

            case MotionEvent.ACTION_MOVE:
                _OnDraw = true;
                drawShape(touchX, touchY);
                break;

            case MotionEvent.ACTION_UP:
                _OnDraw = false;
                if (_ShapeType != 2)
                    _DrawPath.reset();
                drawShape(touchX, touchY);
                _DrawCanvas.drawPath(_DrawPath, _DrawPaint);
                _DrawPath.reset();
                break;

            default:
                return false;
        }

        invalidate();
        return true;
    }

    private void drawTriangle(float touchX, float touchY)
    {
        _DrawPath.moveTo((touchX + touchXStart) / 2, touchYStart);
        _DrawPath.lineTo(touchXStart, touchY);
        _DrawPath.lineTo(touchX, touchY);
        _DrawPath.lineTo((touchX + touchXStart) / 2, touchYStart);
    }

    private void drawShape(float touchX, float touchY)
    {
        if (_ShapeType == 0) {
            _DrawPath.addRect(touchXStart, touchYStart, touchX, touchY, Path.Direction.CCW);
        } else if (_ShapeType == 1){
            _DrawPath.addOval(new RectF(touchXStart, touchYStart, touchX, touchY), Path.Direction.CCW);
        } else if (_ShapeType == 2) {
            _DrawPath.lineTo(touchX, touchY);
        }else if (_ShapeType == 4) {
            _DrawPath.setFillType(Path.FillType.EVEN_ODD);
            drawTriangle(touchX, touchY);
        }
    }

    public void setColor(String newColor)
    {
        invalidate();
        _PaintColor = Color.parseColor(newColor);
        _DrawPaint.setColor(_PaintColor);
    }

    public void setFormShape(int shapeType)
    {
        invalidate();
        if (shapeType == 2)
            setLineShape();
        else
            setBasicShape();
        _ShapeType = shapeType;
        _DrawPath.reset();
    }

    private void setBasicShape()
    {
        _DrawPaint.setStyle(Paint.Style.FILL);
        _DrawPaint.setStrokeJoin(Paint.Join.BEVEL);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
    }

    private void setLineShape() {
        _DrawPaint.setStyle(Paint.Style.STROKE);
        _DrawPaint.setStrokeJoin(Paint.Join.ROUND);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
    }

}
