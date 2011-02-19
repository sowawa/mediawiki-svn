//
//  CommonsUpload.h
//  WikiSnaps
//
//  Created by Derk-Jan Hartman on 15-01-11.
//  Copyright 2011 Derk-Jan Hartman
//
//  Dual-licensed MIT and BSD

#import <Foundation/Foundation.h>

@class CommonsUpload;
@protocol CommonsUploadDelegate <NSObject>
@required

- (void)uploadSucceeded;
- (void)uploadFailed:(NSString *)error;

@end


@interface CommonsUpload : NSObject {
	NSData *imageData;
	NSString *title;
	NSString *description;
	NSString *token;
	NSString *editToken;

	id <CommonsUploadDelegate> delegate;
}

@property (nonatomic, retain) NSData *imageData;
@property (nonatomic, retain) NSString *title;
@property (nonatomic, retain) NSString *description;
@property (nonatomic, assign) id <CommonsUploadDelegate> delegate;

- (NSString *)getUploadText;
- (NSString *)getUploadDescription;

- (void)uploadImage;
- (BOOL)verifyTitle:(NSString *)possibleTitle;

@end