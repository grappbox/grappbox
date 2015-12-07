#ifndef IMAGEUPLOADWIDGET_H
#define IMAGEUPLOADWIDGET_H

#include <QWidget>
#include <QPixmap>
#include <QFileDialog>
#include <QLabel>
#include <QPushButton>
#include <QGridLayout>
#include <QStandardPaths>
#include <QImage>
#include <QGraphicsView>
#include <QGraphicsPixmapItem>
#include <QGraphicsScene>
#include <QBuffer>

#define IMG_PREVIEW_WIDTH 128
#define IMG_PREVIEW_HEIGHT IMG_PREVIEW_WIDTH
#define IMG_PREVIEW_SIZE IMG_PREVIEW_WIDTH,IMG_PREVIEW_HEIGHT

class ImageUploadWidget : public QWidget
{
    Q_OBJECT
public:
    explicit            ImageUploadWidget(QWidget *parent = 0);
    virtual             ~ImageUploadWidget();
    void                setImage(const QString &pixmap);
    const QPixmap       &getImage();
    QString             getEncodedImage();
    bool                isImageFromComputer();

public slots:
    void                OnImageSelectTriggered();
    void                OnFitTriggered();
    void                SceneChanged(QList<QRectF>);

signals:
    void                OnImageSelected();

private:
    QHBoxLayout         *_mainLayout;
    QGraphicsView       *_imagePresented;
    QGraphicsScene      *_scene;
    QGraphicsPixmapItem *_currentPixmap;
    QLabel              *_filenameSelected;
    QPushButton         *_selectFileBtn;
    QPushButton         *_fitImg;
    QString             _filename;
    QPixmap             _lastTakenImage;
};

#endif // IMAGEUPLOADWIDGET_H

